<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface\Component;

use MediaWiki\Language\RawMessage;
use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\CommonUserInterface\IDropdownIcon;

class SimpleDropdownIcon extends ComponentBase implements IDropdownIcon {

	/**
	 *
	 * @param array $options
	 */
	public function __construct( $options ) {
		$this->options = array_merge(
			[
				'id' => 'simple-dropdown-icon',
				'title' => new RawMessage( 'SimpleDropdownIcon' ),
				'aria-label' => new RawMessage( 'SimpleDropdownIcon' ),
				'items' => [],
				'container-classes' => [],
				'button-classes' => [],
				'icon-classes' => [],
				'menu-classes' => [],
				'tabindex' => '0'
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
	 * @return array
	 */
	public function getIconClasses(): array {
		return $this->options['icon-classes'];
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

	/**
	 *
	 * @return string
	 */
	public function getTabindex(): string {
		return $this->options['tabindex'];
	}
}
