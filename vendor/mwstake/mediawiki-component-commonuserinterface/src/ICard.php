<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

interface ICard {

	/**
	 * @return string[]
	 */
	public function getContainerClasses(): array;
}
