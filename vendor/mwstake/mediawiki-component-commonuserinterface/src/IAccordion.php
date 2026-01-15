<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

use MediaWiki\Message\Message;

interface IAccordion extends IComponent {
	/**
	 *
	 * @return Message
	 */
	public function getLabel(): Message;
}
