<?php

namespace BlueSpice\UserSidebar\Hook\BSUsageTrackerRegisterCollectors;

use BS\UsageTracker\Hook\BSUsageTrackerRegisterCollectors;

class NoOfUserSidebars extends BSUsageTrackerRegisterCollectors {

	protected function doProcess() {
		$this->collectorConfig['usersidebars'] = [
			'class' => 'Database',
			'config' => [
				'identifier' => 'no-of-user-sidebars',
				'internalDesc' => 'Number of User Sidebars',
				'table' => 'page',
				'uniqueColumns' => [ 'page_title' ],
				'condition' => [
					'page_namespace' => NS_USER,
					'page_title LIKE "%/Sidebar"'
				]
			]
		];
	}
}
