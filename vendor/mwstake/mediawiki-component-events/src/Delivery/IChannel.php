<?php

declare( strict_types=1 );

namespace MWStake\MediaWiki\Component\Events\Delivery;

use MediaWiki\Message\Message;
use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\Events\INotificationEvent;
use MWStake\MediaWiki\Component\Events\Notification;

interface IChannel {

	/**
	 * @return string
	 */
	public function getKey(): string;

	/**
	 * Get display name of the channel
	 *
	 * @return Message
	 */
	public function getLabel(): Message;

	/**
	 * Return true if channel will reject notification
	 *
	 * @param INotificationEvent $event
	 * @param UserIdentity $user
	 *
	 * @return bool
	 */
	public function shouldSkip( INotificationEvent $event, UserIdentity $user ): bool;

	/**
	 * @return array
	 */
	public function getDefaultConfiguration(): array;

	/**
	 * Called when a notification for this channel is persisted
	 *
	 * @param Notification $notification
	 * @param bool $created
	 *
	 * @return void
	 */
	public function onNotificationPersisted( Notification $notification, bool $created ): void;

	/**
	 * @param Notification $notification
	 * @param array &$data
	 * @return void
	 */
	public function onNotificationOutputSerialized( Notification $notification, array &$data ): void;
}
