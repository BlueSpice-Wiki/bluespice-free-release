<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface\Component;

use MediaWiki\Language\RawMessage;
use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\CommonUserInterface\IButton;

class SimpleButton extends ComponentBase implements IButton {

	/**
	 *
	 * @param string $options
	 */
	public function __construct( $options ) {
		$this->options = array_merge( [
			'id' => 'simple-button',
			'aria-label' => new RawMessage( 'SimpleButton' ),
			'text' => new RawMessage( 'SimpleButton' ),
			'classes' => [],
			'disabled' => false
		], $options );
	}

	/**
	 * @inheritDoc
	 */
	public function getId(): string {
		return $this->options['id'];
	}

	/**
	 *
	 * @return Message
	 */
	public function getAriaLabel(): Message {
		return $this->options['aria-label'];
	}

	/**
	 * @return Message
	 */
	public function getText(): Message {
		return $this->options['text'];
	}

	/**
	 * @inheritDoc
	 */
	public function getClasses(): array {
		return $this->options['classes'];
	}

	/**
	 *
	 * @return bool
	 */
	public function isDisabled(): bool {
		return $this->options['disabled'];
	}
}
