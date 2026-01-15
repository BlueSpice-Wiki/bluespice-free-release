<?php

namespace BlueSpice\Discovery\Component;

use BlueSpice\Discovery\CookieHandler;
use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleLink;

class SidebarSecondaryToggleButton extends SimpleLink {
	/**
	 *
	 * @param CookieHandler $cookieHandler
	 */
	public function __construct( $cookieHandler ) {
		parent::__construct( [] );

		$this->isExpanded = $cookieHandler->getCookie( 'sb-sec-cnt' );
	}

	/**
	 * @inheritDoc
	 */
	public function getId(): string {
		return 'sb-sec-tgl-btn';
	}

	/**
	 * @inheritDoc
	 */
	public function getSubComponents(): array {
		return $this->options['items'];
	}

	/**
	 * @inheritDoc
	 */
	public function getClasses(): array {
		return [ 'ico-btn', 'bi-wrench', 'sb-toggle' ];
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
		return $this->getButtonTitle();
	}

	/**
	 * @inheritDoc
	 */
	public function getAriaLabel(): Message {
		return $this->getButtonAriaLabel();
	}

	/**
	 * @inheritDoc
	 */
	public function getDataAttributes(): array {
		return [
			'bs-target' => '#sb-sec-cnt'
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getAriaAttributes(): array {
		return [
			'expanded' => $this->getExpandedState(),
			'controls' => 'sb-sec-cnt'
		];
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
	private function getButtonTitle(): Message {
		if ( $this->isExpanded === 'true' ) {
			return Message::newFromKey( 'bs-discovery-sidebar-secondary-toggle-hide-title' );
		} else {
			return Message::newFromKey( 'bs-discovery-sidebar-secondary-toggle-show-title' );
		}
	}

	/**
	 *
	 * @return Message
	 */
	private function getButtonAriaLabel(): Message {
		if ( $this->isExpanded === 'true' ) {
			return Message::newFromKey( 'bs-discovery-sidebar-secondary-toggle-hide-aria-label' );
		} else {
			return Message::newFromKey( 'bs-discovery-sidebar-secondary-toggle-show-aria-label' );
		}
	}

	/**
	 *
	 * @return string
	 */
	private function getExpandedState(): string {
		if ( $this->isExpanded !== null ) {
			return $this->isExpanded;
		} else {
			return 'false';
		}
	}
}
