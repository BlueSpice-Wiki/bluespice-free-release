<?php

namespace BlueSpice\Discovery\Hook\BSUsageTrackerRegisterCollectors;

use BS\UsageTracker\Hook\BSUsageTrackerRegisterCollectors;

class MediaWikiSidebarModified extends BSUsageTrackerRegisterCollectors {

	protected function doProcess() {
		$this->collectorConfig['mediawiki-sidebar-modified'] = [
			'class' => 'ModifiedPage',
			'config' => [
				'identifier' => 'mediawiki-sidebar-modified',
				'internalDesc' => 'Is the MediaWiki Sidebar modified?',
				'namespace' => NS_MEDIAWIKI,
				'title' => 'Sidebar',
				'modifiedrevision' => 2,
			]
		];
	}
}
