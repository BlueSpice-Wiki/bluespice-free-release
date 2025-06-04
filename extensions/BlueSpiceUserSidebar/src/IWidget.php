<?php

namespace BlueSpice\UserSidebar;

use MediaWiki\Message\Message;

interface IWidget {
	/**
	 *
	 * @return string
	 */
	public function getKey(): string;

	/**
	 *
	 * @return Message
	 */
	public function getHeaderMessage(): Message;

	/**
	 *
	 * @return array
	 */
	public function getLinks(): array;
}
