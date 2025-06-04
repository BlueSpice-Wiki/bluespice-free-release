<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\NotifyMe\Event;

use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\Title\Title;
use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\Events\PriorityEvent;

class EditRevertedEvent extends EditPageEvent implements PriorityEvent {
	/** @var array */
	protected $affectedUsers;

	/**
	 * @param array $affectedUsers
	 * @param UserIdentity $agent
	 * @param Title $title
	 * @param int $revId
	 * @param int $diffTarget
	 */
	public function __construct(
		array $affectedUsers, UserIdentity $agent, Title $title, int $revId, int $diffTarget
	) {
		parent::__construct( $agent, $title, $revId, $diffTarget );
		$this->affectedUsers = $affectedUsers;
	}

	/**
	 * @return string
	 */
	public function getKey(): string {
		return 'page-edit-revert';
	}

	/**
	 * @return Message
	 */
	public function getKeyMessage(): Message {
		return Message::newFromKey( 'notifyme-event-page-edit-revert-key-desc' );
	}

	/**
	 * @return string
	 */
	protected function getMessageKey(): string {
		return 'notifyme-event-page-edit-revert';
	}

	/**
	 * @return array|null
	 */
	public function getPresetSubscribers(): ?array {
		return $this->affectedUsers;
	}

	/**
	 * @inheritDoc
	 */
	public static function getArgsForTesting(
		UserIdentity $agent, MediaWikiServices $services, array $extra = []
	): array {
		$params = parent::getArgsForTesting( $agent, $services, $extra );
		$params[] = 2;
		$params[] = 1;
		array_unshift(
			$params, [
				$extra['targetUser'] ?? $services->getUserFactory()->newFromName( 'WikiSysop' ) ]
		);

		return $params;
	}
}
