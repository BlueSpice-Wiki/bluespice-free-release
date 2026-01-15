<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs\Hook;

use MWStake\MediaWiki\Component\CommonWebAPIs\Rest\QueryStore;
use MWStake\MediaWiki\Component\DataStore\ResultSet;

interface MWStakeCommonWebAPIsQueryStoreResultHook {
	/**
	 * This hook is called after a query store has been executed
	 *
	 * @since 1.35
	 *
	 * @param QueryStore $store
	 * @param ResultSet &$result
	 * @return void
	 */
	public function onMWStakeCommonWebAPIsQueryStoreResult( $store, &$result );
}
