<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface\Component;

use MediaWiki\Language\RawMessage;
use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\CommonUserInterface\IDropdown;

class SimpleDropdown extends ComponentBase implements IDropdown {

	/**
	 *
	 * @param array $options
	 */
	public function __construct( $options ) {
		$this->options = array_merge(
			[
				'id' => 'simple-dropdown',
				'text' => new RawMessage( 'simple dropdown' ),
				'title' => new RawMessage( 'simple dropdown' ),
				'aria-label' => new RawMessage( 'simple dropdown' ),
				'items' => [],
				'container-classes' => [],
				'button-classes' => [],
				'menu-classes' => []
			],
			$options
		);
	}

	/**
	 * @inheritDoc
	 */
	public function getId(): string {
		return $this->options['id'];
	}

	/**
	 * @inheritDoc
	 */
	public function getSubComponents(): array {
		return $this->options['items'];
	}

	/**
	 * @return array
	 */
	public function getContainerClasses(): array {
		return $this->options['container-classes'];
	}

	/**
	 * @return array
	 */
	public function getButtonClasses(): array {
		return $this->options['button-classes'];
	}

	/**
	 * @return array
	 */
	public function getMenuClasses(): array {
		return $this->options['menu-classes'];
	}

	/**
	 * @return Message
	 */
	public function getText(): Message {
		return $this->options['text'];
	}

	/**
	 * @return Message
	 */
	public function getTitle(): Message {
		return $this->options['title'];
	}

	/**
	 * @return Message
	 */
	public function getAriaLabel(): Message {
		return $this->options['aria-label'];
	}
}
