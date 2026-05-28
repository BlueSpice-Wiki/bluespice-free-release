<?php

namespace MWStake\MediaWiki\Component\Utils;

use LocalisationCache;
use MediaWiki\Language\LanguageCode;
use Wikimedia\Rdbms\ILoadBalancer;

class MessageHelper {

	/** @var string */
	private string $langCode;
	/** @var array|null */
	private ?array $messagePages = null;

	/**
	 * @param LocalisationCache $localizationCache
	 * @param ILoadBalancer $lb
	 * @param LanguageCode $langCode
	 */
	public function __construct(
		private readonly LocalisationCache $localizationCache,
		private readonly ILoadBalancer $lb,
		LanguageCode $langCode
	) {
		$this->langCode = $langCode?->toString() ?? 'en';
	}

	/**
	 * Checks registered message keys + NS_MEDIAWIKI pages
	 *
	 * @param string $key
	 * @return bool
	 */
	public function msgExists( string $key ): bool {
		if ( $this->msgExistsQuick( $key ) ) {
			return true;
		}
		$this->assertPages();
		return in_array( mb_strtolower( $key ), $this->messagePages, true );
	}

	/**
	 * Checks only registered message keys, without checking NS_MEDIAWIKI pages
	 * About 3x faster than classic Message::exists
	 *
	 * @param string $key
	 * @return bool
	 */
	public function msgExistsQuick( string $key ): bool {
		return $this->localizationCache->getSubitem( 'en', 'messages', $key ) !== null;
	}

	/**
	 * @return void
	 */
	private function assertPages(): void {
		if ( $this->messagePages === null ) {
			$query = $this->lb->getConnection( DB_REPLICA )->newSelectQueryBuilder();
			$query
				->select( 'page_title' )
				->from( 'page' )
				->where( [ 'page_namespace' => NS_MEDIAWIKI, 'page_content_model' => 'wikitext' ] )
				->caller( __METHOD__ );

			$this->messagePages = [];
			foreach ( $query->fetchResultSet() as $row ) {
				$this->messagePages[] = mb_strtolower( $row->page_title );
			}
		}
	}
}
