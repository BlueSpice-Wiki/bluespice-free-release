<?php

namespace MediaWiki\Extension\NotifyMe\SubscriberProvider\ManualProvider\SubscriptionSet;

use MediaWiki\Extension\NotifyMe\SubscriberProvider\ManualProvider\ISubscriptionSet;
use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\Events\INotificationEvent;
use WatchedItem;
use WatchedItemStoreInterface;

class WatchlistSet implements ISubscriptionSet {

	/**
	 * @var WatchedItemStoreInterface
	 */
	private $watchedItemStore;

	/**
	 * @param WatchedItemStoreInterface $watchedItemStore
	 */
	public function __construct( WatchedItemStoreInterface $watchedItemStore ) {
		$this->watchedItemStore = $watchedItemStore;
	}

	/**
	 * @inheritDoc
	 */
	public function isSubscribed( array $setData, INotificationEvent $event, UserIdentity $user ): bool {
		$item = $this->watchedItemStore->getWatchedItem( $user, $event->getTitle() );
		if ( !( $item instanceof WatchedItem ) ) {
			return false;
		}
		if ( $item->isExpired() ) {
			return false;
		}
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function getClientSideModule(): string {
		return 'ext.notifyme.subscription.set';
	}
}
