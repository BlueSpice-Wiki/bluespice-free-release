<?php

namespace BlueSpice\WatchList\Tag;

use BlueSpice\Tag\Handler;
use BsStringHelper;
use MediaWiki\Context\RequestContext;
use MediaWiki\MediaWikiServices;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;
use MediaWiki\Title\Title;

class WatchListHandler extends Handler {

	/** @var MessageLocalizer */
	private $messageLocalizer;

	/**
	 *
	 * @param string $processedInput
	 * @param array $processedArgs
	 * @param Parser $parser
	 * @param PPFrame $frame
	 */
	public function __construct( $processedInput, array $processedArgs, Parser $parser,
	PPFrame $frame ) {
		parent::__construct( $processedInput, $processedArgs, $parser, $frame );
		$this->messageLocalizer = RequestContext::getMain();
	}

	/**
	 * @return string
	 */
	public function handle() {
		$this->parser->getOutput()->setPageProperty( 'bs-tag-watchlist', 1 );
		$list = '';
		if ( !$this->parser->getUserIdentity()->isRegistered() ) {
			return $this->messageLocalizer->msg( 'bs-watchlist-tag-watchlist-no-user' )->text();
		}
		$titles = $this->getWatchlistTitles();
		if ( empty( $titles ) ) {
			return $this->messageLocalizer->msg( 'bs-watchlist-tag-watchlist-no-entries' )->text();
		}
		foreach ( $titles as $title ) {
			$displayText = BsStringHelper::shorten( $title->getPrefixedText(), [
				'max-length' => $this->processedArgs[WatchList::ATTR_MAX_TITLE_LENGTH],
				'position' => 'middle'
			] );
			$list .= "* [[{$title->getPrefixedText()}|$displayText]]\n";
		}
		return $this->parser->recursiveTagParseFully( $list );
	}

	/**
	 * @return Title[]
	 */
	protected function getWatchlistTitles() {
		$watchlist = [];

		$options = [];
		if ( $this->processedArgs[WatchList::ATTR_ORDER] === WatchList::ATTR_ORDER_PAGE_NAME ) {
			$options['ORDER BY'] = 'wl_title';
		}
		$options['LIMIT'] = $this->processedArgs[WatchList::ATTR_COUNT];

		$dbr = MediaWikiServices::getInstance()->getDBLoadBalancer()
			->getConnection( DB_REPLICA );
		$res = $dbr->select(
			[ 'watchlist', 'page' ],
			[ 'wl_namespace', 'wl_title' ],
			[
				'wl_user' => $this->parser->getUserIdentity()->getId(),
				'NOT wl_notificationtimestamp' => null,
				'wl_title = page_title',
				'wl_namespace = page_namespace',
				"page_content_model" => [ "", "wikitext" ]
			],
			__METHOD__,
			$options
		);

		foreach ( $res as $row ) {
			$watchedTitle = Title::newFromText( $row->wl_title, $row->wl_namespace );
			if ( !$watchedTitle || !$watchedTitle->exists() ) {
				continue;
			}

			$watchlist[] = $watchedTitle;
		}

		return $watchlist;
	}
}
