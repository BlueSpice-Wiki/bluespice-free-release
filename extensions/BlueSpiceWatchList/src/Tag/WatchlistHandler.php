<?php

namespace BlueSpice\WatchList\Tag;

use BsStringHelper;
use MediaWiki\Message\Message;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;
use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\GenericTagHandler\ITagHandler;
use Wikimedia\Rdbms\ILoadBalancer;

class WatchlistHandler implements ITagHandler {

	/**
	 * @param ILoadBalancer $lb
	 * @param TitleFactory $titleFactory
	 */
	public function __construct(
		private readonly ILoadBalancer $lb,
		private readonly TitleFactory $titleFactory
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function getRenderedContent( string $input, array $params, Parser $parser, PPFrame $frame ): string {
		$parser->getOutput()->setPageProperty( 'bs-tag-watchlist', 1 );
		$list = '';
		if ( !$parser->getUserIdentity()->isRegistered() ) {
			return Message::newFromKey( 'bs-watchlist-tag-watchlist-no-user' )->text();
		}
		$titles = $this->getWatchlistTitles( $params, $parser->getUserIdentity() );
		if ( empty( $titles ) ) {
			return Message::newFromKey( 'bs-watchlist-tag-watchlist-no-entries' )->text();
		}
		foreach ( $titles as $title ) {
			$displayText = BsStringHelper::shorten( $title->getPrefixedText(), [
				'max-length' => $params['maxtitlelength'],
				'position' => 'middle'
			] );
			$list .= "* [[{$title->getPrefixedText()}|$displayText]]\n";
		}
		return $parser->recursiveTagParseFully( $list );
	}

	/**
	 * @return Title[]
	 */
	protected function getWatchlistTitles( array $params, UserIdentity $user ): array {
		$watchlist = [];

		$options = [];
		if ( $params['order'] === 'pagename' ) {
			$options['ORDER BY'] = 'wl_title';
		}
		$options['LIMIT'] = $params['count'];

		$dbr = $this->lb->getConnection( DB_REPLICA );
		$res = $dbr->select(
			[ 'watchlist', 'page' ],
			[ 'wl_namespace', 'wl_title' ],
			[
				'wl_user' => $user->getId(),
				'NOT wl_notificationtimestamp' => null,
				'wl_title = page_title',
				'wl_namespace = page_namespace',
				"page_content_model" => [ "", "wikitext" ]
			],
			__METHOD__,
			$options
		);

		foreach ( $res as $row ) {
			$watchedTitle = $this->titleFactory->makeTitle( $row->wl_namespaces, $row->wl_title );
			if ( !$watchedTitle->exists() ) {
				continue;
			}

			$watchlist[] = $watchedTitle;
		}

		return $watchlist;
	}
}
