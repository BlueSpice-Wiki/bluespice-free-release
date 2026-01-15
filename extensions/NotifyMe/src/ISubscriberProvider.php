<?php

namespace MediaWiki\Extension\NotifyMe;

use MediaWiki\Message\Message;
use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\Events\Delivery\IChannel;
use MWStake\MediaWiki\Component\Events\INotificationEvent;
use MWStake\MediaWiki\Component\Events\Notification;

interface ISubscriberProvider {
	/**
	 * @return string
	 */
	public function getKey(): string;

	/**
	 * @param INotificationEvent $event
	 * @param IChannel $channel
	 *
	 * @return UserIdentity[]
	 */
	public function getSubscribers( INotificationEvent $event, IChannel $channel ): array;

	/**
	 * @param Notification $notification
	 *
	 * @return Message
	 */
	public function getDescription( Notification $notification ): Message;

	/**
	 * Link to the page where the user can change their preferences regarding this subscriber provider
	 *
	 * @return string|null if not configurable
	 */
	public function getConfigurationLink(): ?string;
}
