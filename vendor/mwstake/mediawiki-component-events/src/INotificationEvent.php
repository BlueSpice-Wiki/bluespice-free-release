<?php

namespace MWStake\MediaWiki\Component\Events;

use DateTime;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\Events\Delivery\IChannel;

interface INotificationEvent {
	/**
	 * Unique event key, as specified in the event registration
	 *
	 * @return string
	 */
	public function getKey(): string;

	/**
	 * Message describing event type
	 *
	 * @return Message
	 */
	public function getKeyMessage(): Message;

	/**
	 * @param IChannel $forChannel
	 * @return Message
	 */
	public function getMessage( IChannel $forChannel ): Message;

	/**
	 * @return string Icon name
	 */
	public function getIcon(): string;

	/**
	 * @return UserIdentity
	 */
	public function getAgent(): UserIdentity;

	/**
	 * @return DateTime
	 */
	public function getTime(): DateTime;

	/**
	 * Message to be shown before the list of links (if applicable)
	 * @param IChannel $forChannel
	 * @return Message|null
	 */
	public function getLinksIntroMessage( IChannel $forChannel ): ?Message;

	/**
	 * @param DataTime $time
	 * @return void
	 */
	public function setTime( DateTime $time ): void;

	/**
	 * @param IChannel $forChannel
	 * @return EventLink[]
	 */
	public function getLinks( IChannel $forChannel ): array;

	/**
	 * List of users that should be notified about this event
	 * If the list is empty, event will be sent to no-one
	 * If the list is not empty, the event will be sent
	 * only to users who are on the list AND subscribed to the event
	 * If return is null, the event will be sent to all users who are subscribed to the event
	 *
	 * Use carefully, this should not do the general filtering, its meant just for events with
	 * a very specific set of receivers,
	 * eg. Permission change of particular users, group membership change, etc.
	 *
	 * @return array|null
	 */
	public function getPresetSubscribers(): ?array;

	/**
	 * List of event-keys that this one overrides
	 * This is to be used to suppress notifications that are already covered by this one
	 * It will suppress same-topic events if classnames of those are returned here
	 *
	 * @return array
	 */
	public function hasPriorityOver(): array;

	/**
	 * Returns the arguments needed for testing. These will be used to mock-trigger the event.
	 *
	 * @param UserIdentity $agent The user identity object.
	 * @param MediaWikiServices $services The MediaWiki services object.
	 * @param array|null $extra Extra arguments.
	 * @return array The arguments needed for testing.
	 */
	public static function getArgsForTesting(
		UserIdentity $agent, MediaWikiServices $services, array $extra = []
	): array;
}
