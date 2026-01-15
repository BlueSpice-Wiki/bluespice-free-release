<?php

namespace BlueSpice\Discovery;

use MWStake\MediaWiki\Component\CommonUserInterface\IComponent;

interface IMetaItemProvider {

	/**
	 * @return string
	 */
	public function getName(): string;

	/**
	 * @return IComponent
	 */
	public function getComponent(): IComponent;

}
