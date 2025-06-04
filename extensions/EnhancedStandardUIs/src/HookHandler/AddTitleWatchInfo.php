<?php

namespace MediaWiki\Extension\EnhancedStandardUIs\HookHandler;

use MediaWiki\Context\RequestContext;
use MediaWiki\Title\TitleFactory;
use MediaWiki\Watchlist\WatchlistManager;
use MWStake\MediaWiki\Component\CommonWebAPIs\Hook\MWStakeCommonWebAPIsQueryStoreResultHook;
use MWStake\MediaWiki\Component\CommonWebAPIs\Rest\TitleTreeStore;
use MWStake\MediaWiki\Component\DataStore\ResultSet;

class AddTitleWatchInfo implements MWStakeCommonWebAPIsQueryStoreResultHook {

	/** @var WatchlistManager */
	private $watchlistManager;

	/** @var TitleFactory */
	private $titleFactory;

	/**
	 * @param WatchlistManager $watchlistManager
	 * @param TitleFactory $titleFactory
	 */
	public function __construct( WatchlistManager $watchlistManager, TitleFactory $titleFactory ) {
		$this->watchlistManager = $watchlistManager;
		$this->titleFactory = $titleFactory;
	}

	/**
	 * @inheritDoc
	 */
	public function onMWStakeCommonWebAPIsQueryStoreResult( $store, &$result ) {
		if ( !( $store instanceof TitleTreeStore ) ) {
			return;
		}
		$user = RequestContext::getMain()->getUser();
		if ( $user->isAnon() ) {
			return;
		}
		$data = $result->getRecords();
		foreach ( $data as $record ) {
			$title = $this->titleFactory->newFromText( $record->get( 'prefixed' ) );
			$isWatchable = $this->watchlistManager->isWatchable( $title );
			if ( !$isWatchable ) {
				continue;
			}
			$isWatched = $this->watchlistManager->isWatched( $user, $title );
			$record->set( 'watch', $isWatched );
		}
		$result = new ResultSet( $data, $result->getTotal() );
	}
}
