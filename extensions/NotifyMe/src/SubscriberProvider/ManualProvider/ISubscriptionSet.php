<?php

namespace MediaWiki\Extension\NotifyMe\SubscriberProvider\ManualProvider;

use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\Events\INotificationEvent;

interface ISubscriptionSet {
	/**
	 * Based on the particular set data, determine if the user is subscribed to the event
	 *
	 * @param array $setData
	 * @param INotificationEvent $event
	 * @param UserIdentity $user
	 *
	 * @return bool
	 */
	public function isSubscribed( array $setData, INotificationEvent $event, UserIdentity $user ): bool;

	/**
	 * Name of the RL module that provides client-side integration for this set
	 * @return string
	 */
	public function getClientSideModule(): string;
}
