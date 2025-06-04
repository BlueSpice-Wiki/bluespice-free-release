<?php

namespace MediaWiki\Extension\NotifyMe\Hook;

use MediaWiki\Extension\NotifyMe\Storage\WebNotificationQueryStore;
use MediaWiki\User\UserIdentity;

interface NotifyMeGetFilterMetaHook {

	/**
	 * @param array &$buckets
	 * @param WebNotificationQueryStore $store
	 * @param UserIdentity $forUser
	 * @param string $forStatus
	 * @return void
	 */
	public function onNotifyMeGetFilterMeta(
		array &$buckets, WebNotificationQueryStore $store, UserIdentity $forUser, string $forStatus
	): void;
}
