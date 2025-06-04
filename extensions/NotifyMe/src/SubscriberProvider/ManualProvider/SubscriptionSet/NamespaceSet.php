<?php

namespace MediaWiki\Extension\NotifyMe\SubscriberProvider\ManualProvider\SubscriptionSet;

use MediaWiki\Extension\NotifyMe\SubscriberProvider\ManualProvider\ISubscriptionSet;
use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\Events\INotificationEvent;

class NamespaceSet implements ISubscriptionSet {
	/**
	 * @inheritDoc
	 */
	public function isSubscribed( array $setData, INotificationEvent $event, UserIdentity $user ): bool {
		$nsId = $setData['ns'] ?? null;
		if ( $nsId === null ) {
			return false;
		}
		return $event->getTitle()->getNamespace() === (int)$nsId;
	}

	/**
	 * @inheritDoc
	 */
	public function getClientSideModule(): string {
		return 'ext.notifyme.subscription.set';
	}
}
