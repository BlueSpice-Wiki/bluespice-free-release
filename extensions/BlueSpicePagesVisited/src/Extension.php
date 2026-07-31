<?php

namespace BlueSpice\PagesVisited;

class Extension extends \BlueSpice\Extension {

	/**
	 * Register tag with UsageTracker extension
	 * @param array &$aCollectorsConfig
	 * @return Always true to keep hook running
	 */
	public static function onBSUsageTrackerRegisterCollectors( &$aCollectorsConfig ) {
		$aCollectorsConfig['bs:pagesvisited'] = [
			'class' => 'Property',
			'config' => [
				'identifier' => 'bs-tag-pagesvisited'
			]
		];
		return true;
	}
}
