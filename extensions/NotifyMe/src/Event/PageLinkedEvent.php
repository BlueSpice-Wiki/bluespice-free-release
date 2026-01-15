<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\NotifyMe\Event;

use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\Page\PageIdentity;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\Events\Delivery\IChannel;
use MWStake\MediaWiki\Component\Events\GroupableEvent;
use MWStake\MediaWiki\Component\Events\TitleEvent;

class PageLinkedEvent extends TitleEvent implements GroupableEvent {
	/** @var User|null */
	private $pageCreator;

	/** @var Title */
	private $targetTitle;

	/**
	 * @param UserIdentity $agent
	 * @param PageIdentity $title
	 * @param UserIdentity $pageCreator
	 * @param Title $targetTitle
	 */
	public function __construct(
		UserIdentity $agent, PageIdentity $title, UserIdentity $pageCreator, Title $targetTitle
	) {
		parent::__construct( $agent, $title );
		$this->pageCreator = $pageCreator;
		$this->targetTitle = $targetTitle;
	}

	/**
	 * @return string
	 */
	public function getKey(): string {
		return 'page-linked';
	}

	/**
	 * @return Message
	 */
	public function getKeyMessage(): Message {
		return Message::newFromKey( 'notifyme-event-page-linked-key-desc' );
	}

	/**
	 * @inheritDoc
	 */
	public function getMessage( IChannel $forChannel ): Message {
		$msgKey = 'notifyme-event-page-linked';
		if ( $this->isBotAgent() ) {
			return Message::newFromKey( $msgKey . '-bot' )->params(
				$this->getTitleAnchor( $this->getTitle(), $forChannel ),
				$this->getTitleAnchor( $this->targetTitle, $forChannel ),
			);
		}
		return Message::newFromKey( $msgKey )->params(
			$this->getAgent()->getName(),
			$this->getTitleAnchor( $this->getTitle(), $forChannel ),
			$this->getTitleAnchor( $this->targetTitle, $forChannel ),
		);
	}

	/**
	 * @inheritDoc
	 */
	public function getPresetSubscribers(): ?array {
		if ( !$this->pageCreator || !$this->pageCreator->isRegistered() ) {
			// Send to no-one
			return [];
		}

		return [ $this->pageCreator ];
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
	public function getGroupMessage( $count, IChannel $forChannel ): Message {
		return Message::newFromKey( 'notifyme-event-page-linked-group' )
			->params(
				$this->getTitleAnchor( $this->getTitle(), $forChannel ),
				$this->getTitleAnchor( $this->targetTitle, $forChannel ),
				$count
			);
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
		$title = $extra['title'] ?? null;
		return [
			$agent,
			$title,
			$extra['targetUser'] ?? $services->getUserFactory()->newFromName( 'WikiSysop' ),
			$services->getTitleFactory()->newMainPage()
		];
	}
}
