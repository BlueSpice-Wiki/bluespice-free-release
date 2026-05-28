<?php

namespace MWStake\MediaWiki\Component\Utils;

use MediaWiki\Page\PageIdentity;
use Wikimedia\LightweightObjectStore\ExpirationAwareness;
use Wikimedia\Rdbms\ILoadBalancer;

class DisplayTitleHelper {

	public const CACHE_TTL = ExpirationAwareness::TTL_MINUTE * 5;

	/** @var array|null */
	private ?array $displayTitles = null;

	/**
	 * @param \ObjectCacheFactory $cacheFactory
	 * @param ILoadBalancer $lb
	 */
	public function __construct(
		private readonly \ObjectCacheFactory $cacheFactory,
		private readonly ILoadBalancer $lb
	) {
	}

	/**
	 * @param PageIdentity $linkTarget
	 * @return string|null
	 */
	public function getDisplayTitle( PageIdentity $linkTarget ): ?string {
		// for a "Title instance does not represent a proper page,
		// but merely a link target", e.g a special page, getId() won't work
		if ( !$linkTarget->canExist() ) {
			return $linkTarget->getText();
		}
		$this->assertData();
		return $this->displayTitles[$linkTarget->getId()] ?? null;
	}

	/**
	 * @param PageIdentity $page
	 * @return void
	 */
	public function updateForPage( PageIdentity $page ) {
		$this->assertData();
		$db = $this->lb->getConnection( DB_REPLICA );
		$res = $db->newSelectQueryBuilder()
			->select( [ 'pp_value' ] )
			->from( 'page_props' )
			->where( [
				'pp_page' => $page->getId(),
				'pp_propname' => 'displaytitle'
			] )
			->caller( __METHOD__ )
			->fetchField();

		if ( $res !== false ) {
			$this->displayTitles[$page->getId()] = $res;
		} else {
			unset( $this->displayTitles[$page->getId()] );
		}
		$cache = $this->cacheFactory->getLocalServerInstance();
		$cc = $cache->makeKey( 'mediawiki-component-utils', 'displaytitles' );
		$cache->set( $cc, $this->displayTitles, static::CACHE_TTL );
	}

	/**
	 * @return void
	 */
	private function assertData() {
		if ( is_array( $this->displayTitles ) ) {
			return;
		}
		$cache = $this->cacheFactory->getLocalServerInstance();
		$cc = $cache->makeKey( 'mediawiki-component-utils', 'displaytitles' );
		$data = $cache->get( $cc );
		if ( is_array( $data ) ) {
			$this->displayTitles = $data;
			return;
		}

		$db = $this->lb->getConnection( DB_REPLICA );
		$res = $db->newSelectQueryBuilder()
			->select( [ 'pp_page', 'pp_value' ] )
			->from( 'page_props' )
			->where( [ 'pp_propname' => 'displaytitle' ] )
			->caller( __METHOD__ )
			->fetchResultSet();

		$this->displayTitles = [];
		foreach ( $res as $row ) {
			$this->displayTitles[(int)$row->pp_page] = $row->pp_value;
		}
		$cache->set( $cc, $this->displayTitles, static::CACHE_TTL );
	}
}
