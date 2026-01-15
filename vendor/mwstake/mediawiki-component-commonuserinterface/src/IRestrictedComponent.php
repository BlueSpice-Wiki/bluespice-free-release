<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

interface IRestrictedComponent {

	/**
	 *
	 * @return string[]
	 */
	public function getPermissions(): array;
}
