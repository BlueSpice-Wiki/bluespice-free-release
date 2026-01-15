<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

interface ILinklistGroupFromArray {

	/**
	 * @return string[]
	 */
	public function getContainerClasses(): array;

	/**
	 * @inheritDoc
	 */
	public function getLinks(): array;

	/**
	 * @return array
	 */
	public function getAriaAttributes(): array;
}
