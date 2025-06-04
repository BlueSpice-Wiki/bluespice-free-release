<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\NotifyMe\Event;

use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\Title\Title;
use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\Events\Delivery\IChannel;
use MWStake\MediaWiki\Component\Events\Delivery\IExternalChannel;
use MWStake\MediaWiki\Component\Events\EventLink;
use MWStake\MediaWiki\Component\Events\GroupableEvent;

class EditPageEvent extends CreatePageEvent implements GroupableEvent {
	/** @var int */
	protected $revId;

	/** @var int|null */
	protected $diffTarget;

	/**
	 * @param UserIdentity $agent
	 * @param Title $title
	 * @param int $revId
	 * @param int $diffTarget
	 */
	public function __construct( UserIdentity $agent, Title $title, int $revId, int $diffTarget ) {
		parent::__construct( $agent, $title );
		$this->revId = $revId;
		$this->diffTarget = $diffTarget;
	}

	/**
	 * @return string
	 */
	public function getKey(): string {
		return 'page-edit';
	}

	/**
	 * @return Message
	 */
	public function getKeyMessage(): Message {
		return Message::newFromKey( 'notifyme-event-page-edit-key-desc' );
	}

	/**
	 * @return string
	 */
	protected function getMessageKey(): string {
		return 'notifyme-event-page-edit';
	}

	/**
	 * @inheritDoc
	 */
	public function getLinks( IChannel $forChannel ): array {
		$links = [];
		if ( $this->diffTarget ) {
			$links[] = new EventLink(
				$forChannel instanceof IExternalChannel ?
					$this->getTitle()->getFullURL( [
						'diff' => $this->revId,
						'oldid' => $this->diffTarget
					] ) :
					$this->getTitle()->getLocalURL( [
						'diff' => $this->revId,
						'oldid' => $this->diffTarget
					] ),
				Message::newFromKey( 'notifyme-link-label-diff' )
			);
		}

		return $links;
	}

	/**
	 * @inheritDoc
	 */
	public function getLinksIntroMessage( IChannel $forChannel ): ?Message {
		return Message::newFromKey( 'notifyme-links-intro-edit' );
	}

	/**
	 * @inheritDoc
	 */
	public function getGroupMessage( $count, IChannel $forChannel ): Message {
		return Message::newFromKey( 'notifyme-event-page-edit-group' )
			->params( $this->getTitleAnchor( $this->getTitle(), $forChannel ), $count );
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

		return $params;
	}
}
