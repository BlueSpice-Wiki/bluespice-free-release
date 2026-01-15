<?php

namespace BlueSpice\Discovery;

use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\CommonUserInterface\IComponent;

interface IMenuProvider {

	/**
	 * @return string
	 */
	public function getName(): string;

	/**
	 * @return Message
	 */
	public function getLabelMsg(): Message;

	/**
	 * @return Message
	 */
	public function getDescriptionMsg(): Message;

	/**
	 * @return IComponent
	 */
	public function getComponent(): IComponent;

}
