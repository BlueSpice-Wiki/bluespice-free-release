<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\NotifyMe\Event;

use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\Title\Title;
use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\Events\Delivery\IChannel;
use MWStake\MediaWiki\Component\Events\TitleEvent;

class MovePageEvent extends TitleEvent {
	/** @var Title */
	private Title $oldTitle;

	/**
	 * @param UserIdentity $agent
	 * @param Title $newTitle
	 * @param Title $oldTitle
	 */
	public function __construct( UserIdentity $agent, Title $newTitle, Title $oldTitle ) {
		parent::__construct( $agent, $newTitle );
		$this->oldTitle = $oldTitle;
	}

	/**
	 * @return string
	 */
	public function getKey(): string {
		return 'page-move';
	}

	/**
	 * @return Message
	 */
	public function getKeyMessage(): Message {
		return Message::newFromKey( 'notifyme-event-page-move-key-desc' );
	}

	/**
	 * @inheritDoc
	 */
	public function getMessage( IChannel $forChannel ): Message {
		$msgKey = 'notifyme-event-page-move';
		if ( $this->isBotAgent() ) {
			return Message::newFromKey( $msgKey . '-bot' )->params(
				$this->getTitleAnchor( $this->oldTitle, $forChannel ),
				$this->getTitleAnchor( $this->getTitle(), $forChannel )
			);
		}
		return Message::newFromKey( $msgKey )->params(
			$this->getAgent()->getName(),
			$this->getTitleAnchor( $this->oldTitle, $forChannel ),
			$this->getTitleAnchor( $this->getTitle(), $forChannel )
		);
	}

	/**
	 * @inheritDoc
	 */
	public function getLinks( IChannel $forChannel ): array {
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public static function getArgsForTesting(
		UserIdentity $agent, MediaWikiServices $services, array $extra = []
	): array {
		$params = parent::getArgsForTesting( $agent, $services, $extra );
		$params[] = $services->getTitleFactory()->newFromText( 'OldPage' );
		return $params;
	}
}
