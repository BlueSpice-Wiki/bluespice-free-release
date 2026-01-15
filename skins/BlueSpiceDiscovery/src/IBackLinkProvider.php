<?php

namespace BlueSpice\Discovery;

use MediaWiki\Context\IContextSource;
use MediaWiki\Message\Message;

interface IBackLinkProvider {

	/**
	 *
	 * @param IContextSource $context
	 * @return bool
	 */
	public function applies( IContextSource $context ): bool;

	/**
	 *
	 * @return string
	 */
	public function getHref(): string;

	/**
	 *
	 * @return Message
	 */
	public function getLabel(): Message;

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

}
