<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

use HtmlArmor;
use MediaWiki\Message\Message;

interface ITreeLinkNode {

	/**
	 * @return Message
	 */
	public function getText(): Message;

	/**
	 * @return Message
	 */
	public function getTitle(): Message;

	/**
	 * @return string
	 */
	public function getHref(): string;

	/**
	 * @return HtmlArmor
	 */
	public function getPreHtml(): HtmlArmor;
}
