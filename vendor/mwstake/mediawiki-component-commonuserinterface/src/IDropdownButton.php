<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

use MediaWiki\Message\Message;

interface IDropdownButton extends IComponent {

	/**
	 * @return string[]
	 */
	public function getContainerClasses(): array;

	/**
	 * @return string[]
	 */
	public function getButtonClasses(): array;

	/**
	 * @return string[]
	 */
	public function getMenuClasses(): array;

	/**
	 * @return Message
	 */
	public function getText(): Message;

	/**
	 * @return Message
	 */
	public function getTitle(): Message;

	/**
	 * @return Message
	 */
	public function getAriaLabel(): Message;

	/**
	 *
	 * @return bool
	 */
	public function isDisabled(): bool;
}
