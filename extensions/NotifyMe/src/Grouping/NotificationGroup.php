<?php

namespace MediaWiki\Extension\NotifyMe\Grouping;

use MediaWiki\User\User;
use MWStake\MediaWiki\Component\Events\GroupableEvent;
use MWStake\MediaWiki\Component\Events\Notification;

/**
 * Decorator for Notification class
 * Groups multiple notifications into one
 */
class NotificationGroup {
	/** @var Notification[] */
	private $notifications;

	/**
	 * @param Notification[] $notifications
	 */
	public function __construct( array $notifications ) {
		$this->notifications = $notifications;
	}

	/**
	 * @return Notification[]
	 */
	public function getNotifications() {
		return $this->notifications;
	}

	/**
	 * @return GroupableEvent
	 */
	public function getEvent(): GroupableEvent {
		return $this->notifications[0]->getEvent();
	}

	/**
	 * @return User
	 */
	public function getTargetUser() {
		return $this->notifications[0]->getTargetUser();
	}
}
