<?php

namespace BlueSpice\Discovery\Hook\BSUsageTrackerRegisterCollectors;

use BS\UsageTracker\Hook\BSUsageTrackerRegisterCollectors;

class EnhancedSidebarModified extends BSUsageTrackerRegisterCollectors {

	protected function doProcess() {
		$this->collectorConfig['enhanced-sidebar-modified'] = [
			'class' => 'Database',
			'config' => [
				'identifier' => 'enhanced-sidebar-modified',
				'internalDesc' => 'Is the Enhanced Sidebar modified?',
				'table' => 'page',
				'uniqueColumns' => 'page_title',
				'condition' => [
					'page_namespace' => NS_MEDIAWIKI,
					'page_title' => 'Sidebar.json'
				]
			]
		];
	}
}
