<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\NotifyMe\Event;

use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\Events\Delivery\IChannel;
use MWStake\MediaWiki\Component\Events\NotificationEvent;
use MWStake\MediaWiki\Component\Events\PriorityEvent;

class UserGroupAddedEvent extends NotificationEvent implements PriorityEvent {

	/** @var UserIdentity */
	private $targetUser;

	/** @var array */
	protected $groups;

	/**
	 * @param UserIdentity $agent
	 * @param UserIdentity $targetUser
	 * @param array $groups
	 */
	public function __construct(
		UserIdentity $agent, UserIdentity $targetUser, array $groups
	) {
		parent::__construct( $agent );
		$this->targetUser = $targetUser;
		$this->groups = $groups;
	}

	/**
	 * @return string
	 */
	public function getKey(): string {
		return 'user-group-added';
	}

	/**
	 * @return Message
	 */
	public function getKeyMessage(): Message {
		return Message::newFromKey( 'notifyme-event-user-group-added-key-desc' );
	}

	/**
	 * @inheritDoc
	 */
	public function getMessage( IChannel $forChannel ): Message {
		$msgKey = $this->getMessageKey();
		if ( $this->isBotAgent() ) {
			return Message::newFromKey( $msgKey . '-bot' )->params(
				count( $this->groups ), implode( ',', $this->groups )
			);
		}
		return Message::newFromKey( $msgKey )->params(
			$this->getAgent()->getName(), count( $this->groups ), implode( ',', $this->groups )
		);
	}

	/**
	 * @return string
	 */
	protected function getMessageKey(): string {
		return 'notifyme-event-group-assigned';
	}

	/**
	 * @inheritDoc
	 */
	public function getPresetSubscribers(): ?array {
		return [ $this->targetUser ];
	}

	/**
	 * @inheritDoc
	 */
	public function getLinks( IChannel $forChannel ): array {
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
		return [
			$agent,
			$extra['targetUser'] ?? $services->getUserFactory()->newFromName( 'WikiSysop' ),
			[ 'sysop' ]
		];
	}
}
