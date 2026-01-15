<?php

namespace BlueSpice\Discovery\Component;

use BlueSpice\Discovery\CookieHandler;
use MediaWiki\Context\IContextSource;
use MediaWiki\Message\Message;
use MediaWiki\SpecialPage\SpecialPage;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleLink;

class FullscreenButton extends SimpleLink {

	/**
	 *
	 * @var string
	 */
	private $fullscreenMode = '';

	/**
	 *
	 * @param CookieHandler $cookieHandler
	 */
	public function __construct( $cookieHandler ) {
		parent::__construct( [] );
		$this->fullscreenMode = $cookieHandler->getCookie( 'fsMode' );
	}

	/**
	 * @inheritDoc
	 */
	public function getId(): string {
		return 'full-screen-btn';
	}

	/**
	 * @inheritDoc
	 */
	public function getClasses(): array {
		return $this->buildClasses();
	}

	/**
	 * @inheritDoc
	 */
	public function getRole(): string {
		return 'button';
	}

	/**
	 * @inheritDoc
	 */
	public function getTitle(): Message {
		return $this->buildTitle();
	}

	/**
	 * @inheritDoc
	 */
	public function getAriaLabel(): Message {
		return $this->buildAriaLabel();
	}

	/**
	 * @inheritDoc
	 */
	public function getHref(): string {
		return '';
	}

	/**
	 *
	 * @return Message
	 */
	private function buildTitle(): Message {
		if ( $this->fullscreenMode === 'true' ) {
			return Message::newFromKey( 'bs-discovery-navbar-full-screen-button-disable-title' );
		} else {
			return Message::newFromKey( 'bs-discovery-navbar-full-screen-button-enable-title' );
		}
	}

	/**
	 *
	 * @return Message
	 */
	private function buildAriaLabel(): Message {
		if ( $this->fullscreenMode === 'true' ) {
			return Message::newFromKey( 'bs-discovery-navbar-full-screen-button-disable-aria-label' );
		} else {
			return Message::newFromKey( 'bs-discovery-navbar-full-screen-button-enable-aria-label' );
		}
	}

	/**
	 *
	 * @return array
	 */
	private function buildClasses(): array {
		$classes = [ 'ico-btn', 'd-none', 'd-xxl-block' ];

		if ( $this->fullscreenMode === 'true' ) {
			array_push( $classes, 'fs-mode-enabled' );
			array_push( $classes, 'bi-chevron-contract' );
		} else {
			array_push( $classes, 'bi-chevron-expand' );
		}

		return $classes;
	}

	/**
	 *
	 * @param IContextSource $context
	 * @return bool
	 */
	public function shouldRender( IContextSource $context ): bool {
		if ( !parent::shouldRender( $context ) ) {
			return false;
		}
		$specialUserLogin = SpecialPage::getSafeTitleFor( 'Userlogin' );
		$title = $context->getTitle();
		if ( $specialUserLogin->equals( $title ) ) {
			return false;
		}
		return true;
	}
}
