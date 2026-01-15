<?php

declare( strict_types=1 );

namespace MWStake\MediaWiki\Component\Events;

use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\Page\PageIdentity;
use MediaWiki\Title\Title;
use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\Events\Delivery\IChannel;
use MWStake\MediaWiki\Component\Events\Delivery\IExternalChannel;

/**
 * Convenience base class for notification events
 */
abstract class TitleEvent extends NotificationEvent implements ITitleEvent {
	/** @var Title */
	protected $title;

	/**
	 * @param UserIdentity $agent
	 * @param PageIdentity $title
	 */
	public function __construct( UserIdentity $agent, PageIdentity $title ) {
		parent::__construct( $agent );
		if ( !$title instanceof Title ) {
			$title = Title::castFromPageIdentity( $title );
		}
		$this->title = $title;
	}

	/**
	 * @return Title
	 */
	public function getTitle(): Title {
		return $this->title;
	}

	/**
	 * @return Message
	 */

	/**
	 * @param IChannel $forChannel
	 * @return Message
	 */
	public function getMessage( IChannel $forChannel ): Message {
		$params = [];
		$msgKey = $this->getMessageKey();
		if ( $this->isBotAgent() ) {
			$msgKey .= '-bot';
		} else {
			$params[] = $this->getAgent()->getName();
		}
		$params[] = $this->getTitleAnchor( $this->getTitle(), $forChannel );

		return Message::newFromKey( $msgKey )->params( $params );
	}

	/**
	 * @param Title $title
	 * @param IChannel $forChannel
	 * @return string
	 */
	protected function getTitleAnchor( Title $title, IChannel $forChannel, ?string $label = null ): string {
		$label = $label ?? $this->getTitleDisplayText( $title );
		if ( $forChannel instanceof IExternalChannel ) {
			return '[' . $title->getFullURL() . ' ' . $label . ']';
		}

		return '[[' . $title->getPrefixedText() . '|' . $label . ']]';
	}

	/**
	 * @return string
	 */
	protected function getMessageKey(): string {
		// STUB - to be implemented by subclasses
		return '';
	}

	/**
	 * @param Title|null $title
	 * @return string
	 */
	protected function getTitleDisplayText( ?Title $title = null ): string {
		$title = $title ?? $this->getTitle();
		if ( !$title->exists() ) {
			return $title->getPrefixedText();
		}
		$props = MediaWikiServices::getInstance()->getPageProps()
			->getProperties( $title, 'displaytext' );
		if ( isset( $props[$title->getArticleID()] ) ) {
			return $props[$title->getArticleID()];
		}

		return $title->getPrefixedText();
	}

	/**
	 * @inheritDoc
	 */
	public function getLinks( IChannel $forChannel ): array {
		return [
			new EventLink(
				$forChannel instanceof IExternalChannel ?
					$this->getTitle()->getFullURL() : $this->getTitle()->getLocalURL(),
				Message::newFromKey( 'notifyme-link-label-view-page' )
			)
		];
	}

	/**
	 * @inheritDoc
	 */
	public static function getArgsForTesting(
		UserIdentity $agent, MediaWikiServices $services, array $extra = []
	): array {
		if ( isset( $extra['title'] ) ) {
			return [ $agent, $extra['title'] ];
		}
		return parent::getArgsForTesting( $agent, $services, $extra );
	}
}
