<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

use MediaWiki\Message\Message;

interface IButtonGroup {

	/**
	 * @return string[]
	 */
	public function getClasses(): array;

	/**
	 * @return Message
	 */
	public function getAriaLabel(): Message;

	/**
	 * One of the `ARIARole::*` constants
	 *
	 * @return string
	 */
	public function getRole(): string;
}
