<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

interface ITreeContainer {

	/**
	 * @return array
	 */
	public function getAriaAttributes(): array;

	/**
	 * One of the `ARIARole::*` constants
	 *
	 * @return string
	 */
	public function getRole(): string;

}
