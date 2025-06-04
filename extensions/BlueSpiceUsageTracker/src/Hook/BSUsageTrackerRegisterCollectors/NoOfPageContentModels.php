<?php

namespace BlueSpice\UsageTracker\Hook\BSUsageTrackerRegisterCollectors;

use BS\UsageTracker\Collectors\Database;
use BS\UsageTracker\Hook\BSUsageTrackerRegisterCollectors;

class NoOfPageContentModels extends BSUsageTrackerRegisterCollectors {

	protected function doProcess() {
		$this->collectorConfig['contentmodels'] = [
			'class' => Database::class,
			'config' => [
				'identifier' => 'no-of-page_content_models',
				'internalDesc' => 'Number of page content models',
				'table' => 'page',
				'uniqueColumns' => [ '*' ],
				'multipledata' => true,
				'column' => 'page_content_model'
			]
		];
	}

}
