<?php

namespace BlueSpice\PageAssignments\Event;

use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\Events\Delivery\IChannel;

class AssignmentRemoveEvent extends AssignmentAddEvent {

	/**
	 * @return Message
	 */
	public function getKeyMessage(): Message {
		return Message::newFromKey( 'bs-pageassignments-event-assignment-remove-key-desc' );
	}

	/**
	 * @return string
	 */
	public function getMessageKey(): string {
		return 'bs-pageassignments-event-assignment-remove';
	}

	/**
	 * @return string
	 */
	public function getKey(): string {
		return 'bs-pa-assignment-remove';
	}

	/**
	 * @inheritDoc
	 */
	public function getLinksIntroMessage( IChannel $forChannel ): ?Message {
		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function getLinks( IChannel $forChannel ): array {
		return [];
	}
}
