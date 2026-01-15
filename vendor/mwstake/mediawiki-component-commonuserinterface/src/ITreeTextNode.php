<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

use HtmlArmor;
use MediaWiki\Message\Message;

interface ITreeTextNode {

	/**
	 * @return Message
	 */
	public function getText(): Message;

	/**
	 * @return HtmlArmor
	 */
	public function getPreHtml(): HtmlArmor;
}
