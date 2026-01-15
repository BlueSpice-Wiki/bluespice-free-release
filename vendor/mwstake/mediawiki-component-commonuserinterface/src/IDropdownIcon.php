<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

use MediaWiki\Message\Message;

interface IDropdownIcon extends IComponent {

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
	 * @return string[]
	 */
	public function getIconClasses(): array;

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
	 * @return string
	 */
	public function getTabindex(): string;
}
