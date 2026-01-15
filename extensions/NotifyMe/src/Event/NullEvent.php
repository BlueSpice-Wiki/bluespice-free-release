<?php

namespace MediaWiki\Extension\NotifyMe\Event;

use DateTime;
use MediaWiki\Language\RawMessage;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\User\User;
use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\Events\Delivery\IChannel;
use MWStake\MediaWiki\Component\Events\INotificationEvent;

class NullEvent implements INotificationEvent {

	/**
	 * @return string
	 */
	public function getKey(): string {
		return 'null-event';
	}

	/**
	 * @return Message
	 */
	public function getKeyMessage(): Message {
		return new RawMessage( 'null-event' );
	}

	/**
	 * @param IChannel $forChannel
	 * @return Message
	 */
	public function getMessage( IChannel $forChannel ): Message {
		return new RawMessage( 'null-event' );
	}

	/**
	 * @return string
	 */
	public function getIcon(): string {
		return '';
	}

	/**
	 * @return UserIdentity
	 */
	public function getAgent(): UserIdentity {
		return User::newSystemUser( 'MediaWiki default', [ 'steal' => true ] );
	}

	/**
	 * @return DateTime
	 */
	public function getTime(): DateTime {
		return new DateTime();
	}

	/**
	 * @param IChannel $forChannel
	 * @return Message|null
	 */
	public function getLinksIntroMessage( IChannel $forChannel ): ?Message {
		return null;
	}

	/**
	 * @param DateTime $time
	 * @return void
	 */
	public function setTime( DateTime $time ): void {
		// NOOP
	}

	/**
	 * @param IChannel $forChannel
	 * @return array|\MWStake\MediaWiki\Component\Events\EventLink[]
	 */
	public function getLinks( IChannel $forChannel ): array {
		return [];
	}

	/**
	 * @return array|null
	 */
	public function getPresetSubscribers(): ?array {
		return null;
	}

	/**
	 * @return array
	 */
	public function hasPriorityOver(): array {
		return [];
	}

	/**
	 * @param UserIdentity $agent
	 * @param MediaWikiServices $services
	 * @param array $extra
	 * @return array
	 */
	public static function getArgsForTesting(
		UserIdentity $agent, MediaWikiServices $services, array $extra = []
	): array {
		return [];
	}
}
