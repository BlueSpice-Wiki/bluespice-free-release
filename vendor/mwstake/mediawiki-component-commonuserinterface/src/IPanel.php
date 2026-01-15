<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

use MediaWiki\Message\Message;

interface IPanel extends ICard {

	/**
	 * @return Message
	 */
	public function getTitle();

	/**
	 * @return Message
	 */
	public function getTitleTooltip();

	/**
	 * @return ITool[]
	 */
	public function getTools();

	/**
	 * @return IBadge[]
	 */
	public function getBadges();

	/**
	 * @return string[]
	 */
	public function getTriggerRLDependencies();

	/**
	 * @return string
	 */
	public function getTriggerCallbackFunctionName();

	/**
	 * @return array
	 */
	public function getContainerData();

	/**
	 *
	 * @return bool
	 */
	public function getExpandedState();
}
