<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

use MediaWiki\Message\Message;

interface IDropdownSplitButton extends IComponent {

	/**
	 * @return string[]
	 */
	public function getContainerClasses(): array;

	/**
	 * @return string[]
	 */
	public function getButtonGroupClasses(): array;

	/**
	 * @return string[]
	 */
	public function getButtonClasses(): array;

	/**
	 * @return string[]
	 */
	public function getSplitButtonClasses(): array;

	/**
	 * @return string[]
	 */
	public function getMenuClasses(): array;

	/**
	 * @return Message
	 */
	public function getButtonText(): Message;

	/**
	 * @return Message
	 */
	public function getButtonTitle(): Message;

		/**
		 * @return Message
		 */
	public function getSplitButtonTitle(): Message;

	/**
	 * @return Message
	 */
	public function getButtonAriaLabel(): Message;

	/**
	 * @return Message
	 */
	public function getSplitButtonAriaLabel(): Message;

	/**
	 *
	 * @return bool
	 */
	public function buttonIsDisabled(): bool;

	/**
	 *
	 * @return bool
	 */
	public function splitButtonIsDisabled(): bool;
}
