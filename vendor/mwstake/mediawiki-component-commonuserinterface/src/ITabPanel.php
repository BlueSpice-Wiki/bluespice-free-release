<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

use MediaWiki\Context\IContextSource;
use MediaWiki\Message\Message;

interface ITabPanel {

	/**
	 *
	 * @return string
	 */
	public function getId(): string;

	/**
	 *
	 * @return Message
	 */
	public function getText(): Message;

	/**
	 *
	 * @return Message
	 */
	public function getTitle(): Message;

	/**
	 *
	 * @return Message
	 */
	public function getAriaLabel(): Message;

	/**
	 *
	 * @return Message
	 */
	public function getAriaDesc(): Message;

	/**
	 *
	 * @return IComponent[]
	 */
	public function getSubComponents(): array;

	/**
	 *
	 * @param IContextSource $context
	 * @return bool
	 */
	public function shouldRender( IContextSource $context ): bool;

	/**
	 *
	 * @param IContextSource $context
	 * @return bool
	 */
	public function isActive( $context ): bool;
}
