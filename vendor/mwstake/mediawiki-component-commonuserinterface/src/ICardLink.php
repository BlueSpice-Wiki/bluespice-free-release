<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

use MediaWiki\Message\Message;

interface ICardLink {

	/**
	 * @return string[]
	 */
	public function getClasses(): array;

	/**
	 * @return array
	 */
	public function getDataAttributes(): array;

	/**
	 * @return string
	 */
	public function getHref(): string;

	/**
	 * @return Message
	 */
	public function getTitle(): Message;

	/**
	 * @return Message
	 */
	public function getText(): Message;

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

	/**
	 * @inheritDoc
	 */
	public function getRel(): string;
}
