<?php

namespace BlueSpice\PageAssignments\Event;

use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\Page\PageIdentity;
use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\Events\Delivery\IChannel;
use MWStake\MediaWiki\Component\Events\PriorityEvent;
use MWStake\MediaWiki\Component\Events\TitleEvent;

class AssignmentAddEvent extends TitleEvent implements PriorityEvent {

	/**
	 * @var array
	 */
	protected $assignedUsers;

	/**
	 * @param UserIdentity $agent
	 * @param PageIdentity $title
	 * @param array $assignedUsers
	 */
	public function __construct( UserIdentity $agent, PageIdentity $title, array $assignedUsers ) {
		parent::__construct( $agent, $title );
		$this->assignedUsers = $assignedUsers;
	}

	/**
	 * @return Message
	 */
	public function getKeyMessage(): Message {
		return Message::newFromKey( 'bs-pageassignments-event-assignment-add-key-desc' );
	}

	/**
	 * @return string
	 */
	public function getMessageKey(): string {
		return 'bs-pageassignments-event-assignment-add';
	}

	/**
	 * @inheritDoc
	 */
	public function getLinksIntroMessage( IChannel $forChannel ): ?Message {
		return Message::newFromKey( 'bs-pageassignments-event-assignment-links-intro' );
	}

	/**
	 * @return UserIdentity[]|null
	 */
	public function getPresetSubscribers(): ?array {
		return $this->assignedUsers;
	}

	/**
	 * @return string
	 */
	public function getKey(): string {
		return 'bs-pa-assignment-add';
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
		return array_merge( parent::getArgsForTesting( $agent, $services, $extra ), [
			[ $extra['targetUser'] ?? $services->getUserFactory()->newFromName( 'WikiSysop' ) ]
		] );
	}
}
