<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

use MediaWiki\Message\Message;

/**
 * Inspired by https://getbootstrap.com/docs/5.0/components/badge/
 */
interface IBadge {

	/**
	 * @return Message
	 */
	public function getText(): Message;

	/**
	 * @inheritDoc
	 */
	public function getClasses(): array;
}
