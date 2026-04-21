<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs\Hook;

interface MWStakeGroupStoreGroupDisplayNameHook {

	/**
	 * @param string $groupName
	 * @param string &$displayName
	 * @param string $groupType
	 * @return void
	 */
	public function onMWStakeGroupStoreGroupDisplayName(
		string $groupName, string &$displayName, string $groupType
	): void;
}
