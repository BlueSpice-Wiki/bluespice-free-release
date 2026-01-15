<?php

namespace BlueSpice\UsageTracker\Hook\BSUsageTrackerRegisterCollectors;

use BS\UsageTracker\Collectors\Database;
use BS\UsageTracker\Hook\BSUsageTrackerRegisterCollectors;

class NoOfUsersPerGroup extends BSUsageTrackerRegisterCollectors {

	protected function doProcess() {
		$this->collectorConfig['userpergroup'] = [
			'class' => Database::class,
			'config' => [
				'identifier' => 'no-of-users-per-group',
				'internalDesc' => 'Number of users per group',
				'table' => 'user_groups',
				'uniqueColumns' => [ '*' ],
				'multipledata' => true,
				'column' => 'ug_group'
			]
		];
	}

}
