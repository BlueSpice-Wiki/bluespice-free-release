<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

interface IContainer {

	/**
	 * @return string
	 */
	public function getTagName(): string;

	/**
	 * @return array
	 */
	public function getClasses(): array;
}
