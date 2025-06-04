<?php

namespace BlueSpice\UsageTracker\Hook\BSUsageTrackerRegisterCollectors;

use BS\UsageTracker\Collectors\Database;
use BS\UsageTracker\Hook\BSUsageTrackerRegisterCollectors;

class NoOfCategories extends BSUsageTrackerRegisterCollectors {

	protected function doProcess() {
		$this->collectorConfig['categories'] = [
			'class' => Database::class,
			'config' => [
				'identifier' => 'no-of-categories',
				'internalDesc' => 'Number of categories',
				'table' => 'categorylinks',
				'uniqueColumns' => [ 'cl_to' ]
			]
		];
	}

}
