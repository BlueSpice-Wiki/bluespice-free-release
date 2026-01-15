<?php

namespace BlueSpice\Discovery\Component;

use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\Literal;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleLink;

class BackToTopButton extends SimpleLink {

	/**
	 *
	 */
	public function __construct() {
		parent::__construct( [] );
	}

	/**
	 * @inheritDoc
	 */
	public function getId(): string {
		return 'back-to-top';
	}

	/**
	 * @inheritDoc
	 */
	public function getClasses(): array {
		return [ 'back-to-top', 'd-none', 'bi-arrow-up-circle-fill' ];
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
		return Message::newFromKey( 'bs-discovery-back-to-top-text' );
	}

	/**
	 * @inheritDoc
	 */
	public function getAriaLabel(): Message {
		return Message::newFromKey( 'bs-discovery-back-to-top-aria-label' );
	}

	/**
	 * @inheritDoc
	 */
	public function getHref(): string {
		return '';
	}

	/**
	 *
	 * @return array
	 */
	public function getSubComponents(): array {
		return [
			new Literal(
				'back-to-top-text',
				"\0"
			)
		];
	}
}
