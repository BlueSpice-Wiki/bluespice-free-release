<?php

namespace MediaWiki\Extension\EnhancedStandardUIs\HookHandler;

use MediaWiki\Context\RequestContext;
use MediaWiki\Title\TitleFactory;
use MediaWiki\Watchlist\WatchedItemStoreInterface;
use MediaWiki\Watchlist\WatchlistManager;
use MWStake\MediaWiki\Component\CommonWebAPIs\Hook\MWStakeCommonWebAPIsQueryStoreResultHook;
use MWStake\MediaWiki\Component\CommonWebAPIs\Rest\TitleTreeStore;

class AddTitleWatchInfo implements MWStakeCommonWebAPIsQueryStoreResultHook {

	/** @var WatchlistManager */
	private $watchlistManager;

	/** @var TitleFactory */
	private $titleFactory;

	/** @var WatchedItemStoreInterface */
	private $watchedItemStore;

	/**
	 * @param WatchlistManager $watchlistManager
	 * @param TitleFactory $titleFactory
	 * @param WatchedItemStoreInterface $watchedItemStore
	 */
	public function __construct(
		WatchlistManager $watchlistManager,
		TitleFactory $titleFactory,
		WatchedItemStoreInterface $watchedItemStore
	) {
		$this->watchlistManager = $watchlistManager;
		$this->titleFactory = $titleFactory;
		$this->watchedItemStore = $watchedItemStore;
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
		$watchableTitles = [];
		foreach ( $data as &$record ) {
			$title = $this->titleFactory->newFromText( $record->get( 'prefixed' ) );
			if ( $title !== null && $this->watchlistManager->isWatchable( $title ) ) {
				$watchableTitles[$record->get( 'id' )] = $title;
			}
		}

		$watchedSet = [];
		if ( $watchableTitles ) {
			$watchedItems = $this->watchedItemStore->loadWatchedItemsBatch( $user, array_values( $watchableTitles ) );
			if ( $watchedItems ) {
				foreach ( $watchedItems as $item ) {
					$target = $item->getTarget();
					$watchedSet[$target->getNamespace() . ':' . $target->getDBkey()] = true;
				}
			}
		}

		foreach ( $data as &$record ) {
			if ( !isset( $watchableTitles[$record->get( 'id' )] ) ) {
				continue;
			}
			$title = $watchableTitles[$record->get( 'id' )];
			$record->set( 'watch', isset( $watchedSet[$title->getNamespace() . ':' . $title->getDBkey()] ) );
		}

		unset( $record );
	}
}
