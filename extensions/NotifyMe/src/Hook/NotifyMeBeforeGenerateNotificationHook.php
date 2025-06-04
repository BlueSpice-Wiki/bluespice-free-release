<?php

namespace MediaWiki\Extension\NotifyMe\Hook;

use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\Events\INotificationEvent;

interface NotifyMeBeforeGenerateNotificationHook {
	/**
	 * @param INotificationEvent $event
	 * @param UserIdentity $forUser
	 * @param array $providers
	 * @param bool &$prevent
	 * @return bool
	 */
	public function onNotifyMeBeforeGenerateNotification(
		INotificationEvent $event, UserIdentity $forUser, array $providers, bool &$prevent
	): bool;

}
