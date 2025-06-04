<?php

namespace BlueSpice\ContextMenu;

use MediaWiki\Context\IContextSource;
use MediaWiki\Message\Message;

interface IMenuItem {

	/**
	 * @return Message
	 */
	public function getLabelMessage();

	/**
	 * @return string
	 */
	public function getUrl();

	/**
	 *
	 * e.g. "bs.pageassignments.contextmenu.handler"
	 *
	 * @return string
	 */
	public function getJSHandler();

	/**
	 * @return string
	 */
	public function getIconClass();

	/**
	 * @return string
	 */
	public function getId();

	/**
	 * @return IMenuItem[]
	 */
	public function getChildren();

	/**
	 * @param IContextSource $context
	 * @return bool
	 */
	public function shouldList( $context );

	/**
	 * @return int
	 */
	public function getPosition();

	/**
	 * @return array
	 */
	public function getFlags(): array;

	/**
	 * @return bool
	 */
	public function isPrimary(): bool;

	/**
	 * Key that this item overrides
	 * @return string|null
	 */
	public function getOverride(): ?string;
}
