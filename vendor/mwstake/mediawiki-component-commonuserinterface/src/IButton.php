<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

use MediaWiki\Message\Message;

interface IButton extends IComponent {

	/**
	 *
	 * @return Message
	 */
	public function getAriaLabel(): Message;

	/**
	 * @return Message
	 */
	public function getText(): Message;

	/**
	 * @inheritDoc
	 */
	public function getClasses(): array;

	/**
	 *
	 * @return bool
	 */
	public function isDisabled(): bool;
}
