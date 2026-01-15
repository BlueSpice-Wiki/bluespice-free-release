<?php

namespace BlueSpice\UsageTracker\Hook\BSUsageTrackerRegisterCollectors;

use BS\UsageTracker\Collectors\Database;
use BS\UsageTracker\Hook\BSUsageTrackerRegisterCollectors;

class NoOfPagesByPageProperty extends BSUsageTrackerRegisterCollectors {

	protected function doProcess() {
		$this->collectorConfig['pagesbypageproperty'] = [
			'class' => Database::class,
			'config' => [
				'identifier' => 'no-of-pages-by-page-property',
				'internalDesc' => 'Number of pages by page property',
				'table' => 'page_props',
				'uniqueColumns' => [ '*' ],
				'multipledata' => true,
				'column' => 'pp_propname'
			]
		];
	}

}
