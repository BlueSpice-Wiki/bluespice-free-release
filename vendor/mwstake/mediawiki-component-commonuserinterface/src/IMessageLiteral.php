<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

use MediaWiki\Message\Message;

interface IMessageLiteral {

	/**
	 * @return Message
	 */
	public function getText(): Message;
}
