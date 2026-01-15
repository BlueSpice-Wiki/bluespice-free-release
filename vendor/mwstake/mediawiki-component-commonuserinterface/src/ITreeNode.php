<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

interface ITreeNode {

	/**
	 * @return bool
	 */
	public function isExpanded(): bool;

	/**
	 * @return array
	 */
	public function getClasses(): array;

	/**
	 * One of the `ARIARole::*` constants
	 *
	 * @return string
	 */
	public function getRole(): string;

	/**
	 * @param array $options
	 * @return void
	 */
	public function setNodeOptions( array $options ): void;
}
