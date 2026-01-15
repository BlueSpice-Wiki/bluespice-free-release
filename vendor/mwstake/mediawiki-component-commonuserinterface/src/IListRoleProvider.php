<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

interface IListRoleProvider {

	/**
	 * @return string
	 */
	public function getContainerRole(): string;

	/**
	 * @return string
	 */
	public function getItemRole(): string;
}
