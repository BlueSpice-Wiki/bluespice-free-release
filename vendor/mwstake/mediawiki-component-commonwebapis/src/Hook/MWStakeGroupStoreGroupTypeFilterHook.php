<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs\Hook;

interface MWStakeGroupStoreGroupTypeFilterHook {

	/**
	 * @param array &$types
	 * @return void
	 */
	public function onMWStakeGroupStoreGroupTypeFilter( array &$types );
}
