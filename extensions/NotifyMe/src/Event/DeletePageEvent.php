<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\NotifyMe\Event;

use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\Events\Delivery\IChannel;
use MWStake\MediaWiki\Component\Events\TitleEvent;

class DeletePageEvent extends TitleEvent {
	/**
	 * @return string
	 */
	public function getKey(): string {
		return 'page-delete';
	}

	/**
	 * @return string
	 */
	protected function getMessageKey(): string {
		return 'notifyme-event-page-delete';
	}

	/**
	 * @return Message
	 */
	public function getKeyMessage(): Message {
		return Message::newFromKey( 'notifyme-event-page-delete-key-desc' );
	}

	/**
	 * @inheritDoc
	 */
	public function getLinks( IChannel $forChannel ): array {
		return [];
	}
}
