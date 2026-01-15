<?php

namespace BlueSpice\Discovery\Hook\BSUsageTrackerRegisterCollectors;

use BS\UsageTracker\Hook\BSUsageTrackerRegisterCollectors;
use MediaWiki\MediaWikiServices;

class EnhancedSidebarActive extends BSUsageTrackerRegisterCollectors {

	private const ENHANCED_SIDEBAR = 'enhanced-mediawiki-sidebar';

	protected function doProcess() {
		$activeSidebar = MediaWikiServices::getInstance()->getConfigFactory()
			->makeConfig( 'bsg' )->get( 'DiscoverySidebarPrimaryMainTabPanelMenu' );
		$isActive = ( $activeSidebar === self::ENHANCED_SIDEBAR ) ? 1 : 0;

		$this->collectorConfig['enhanced-sidebar-active'] = [
			'class' => 'Basic',
			'config' => [
				'identifier' => 'enhanced-sidebar-active',
				'internalDesc' => 'Is the Enhanced Sidebar active?',
				'count' => $isActive
			]
		];
	}
}
