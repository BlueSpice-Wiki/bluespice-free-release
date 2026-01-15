<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

use MediaWiki\Message\Message;

interface IDropdownIconSplitButton extends IComponent {

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
	 * @return string
	 */
	public function getHref(): string;

	/**
	 * @return string[]
	 */
	public function getSplitButtonClasses(): array;

	/**
	 * @return string[]
	 */
	public function getMenuClasses(): array;

	/**
	 * @return array
	 */
	public function getIconClasses(): array;

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
	 * @return string
	 */
	public function getButtonRel(): string;

	/**
	 *
	 * @return bool
	 */
	public function splitButtonIsDisabled(): bool;
}
