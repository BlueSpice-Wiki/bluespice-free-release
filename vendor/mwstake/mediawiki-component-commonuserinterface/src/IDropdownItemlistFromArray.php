<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

interface IDropdownItemlistFromArray {

	/**
	 * @return string[]
	 */
	public function getClasses(): array;

	/**
	 * @inheritDoc
	 */
	public function getLinks(): array;
}
