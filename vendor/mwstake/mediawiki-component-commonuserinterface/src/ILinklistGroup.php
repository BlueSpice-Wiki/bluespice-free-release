<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

interface ILinklistGroup {

	/**
	 * @return string[]
	 */
	public function getClasses(): array;

	/**
	 * @return array
	 */
	public function getAriaAttributes(): array;
}
