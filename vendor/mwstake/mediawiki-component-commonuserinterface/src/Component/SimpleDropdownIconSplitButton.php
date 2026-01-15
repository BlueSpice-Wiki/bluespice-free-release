<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface\Component;

use MediaWiki\Language\RawMessage;
use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\CommonUserInterface\IDropdownIconSplitButton;

class SimpleDropdownIconSplitButton extends ComponentBase implements IDropdownIconSplitButton {

	/**
	 *
	 * @param array $options
	 */
	public function __construct( $options ) {
		$this->options = array_merge(
			[
				'id' => 'simple-dropdown-split-button',
				'icon-classes' => [],
				'button-title' => new RawMessage( 'SimpleSplitButton' ),
				'button-aria-label' => new RawMessage( 'SimpleSplitButton' ),
				'split-button-title' => new RawMessage( 'SimpleSplitButton' ),
				'split-button-aria-label' => new RawMessage( 'SimpleSplitButton' ),
				'button-href' => '',
				'button-rel' => '',
				'button-disabled' => false,
				'split-button-disabled' => false,
				'items' => [],
				'container-classes' => [],
				'button-group-classes' => [],
				'button-classes' => [],
				'split-button-classes' => [],
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
	 * @return string
	 */
	public function getHref(): string {
		return $this->options['button-href'];
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
	public function getButtonGroupClasses(): array {
		return $this->options['button-group-classes'];
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
	public function getSplitButtonClasses(): array {
		return $this->options['split-button-classes'];
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
	public function getIconClasses(): array {
		return $this->options['icon-classes'];
	}

	/**
	 * @return Message
	 */
	public function getButtonTitle(): Message {
		return $this->options['button-title'];
	}

	/**
	 * @return Message
	 */
	public function getSplitButtonTitle(): Message {
		return $this->options['split-button-title'];
	}

	/**
	 * @return Message
	 */
	public function getButtonAriaLabel(): Message {
		return $this->options['button-aria-label'];
	}

	/**
	 * @return Message
	 */
	public function getSplitButtonAriaLabel(): Message {
		return $this->options['split-button-aria-label'];
	}

	/**
	 *
	 * @return bool
	 */
	public function buttonIsDisabled(): bool {
		return $this->options['button-disabled'];
	}

		/**
		 *
		 * @return bool
		 */
	public function splitButtonIsDisabled(): bool {
		return $this->options['split-button-disabled'];
	}

	/**
	 * @inheritDoc
	 */
	public function getButtonRel(): string {
		return $this->options['button-rel'];
	}
}
