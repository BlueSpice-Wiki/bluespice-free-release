<?php

namespace BlueSpice\Discovery\Hook\BSUsageTrackerRegisterCollectors;

use BS\UsageTracker\Hook\BSUsageTrackerRegisterCollectors;
use MediaWiki\MediaWikiServices;

class MediaWikiSidebarActive extends BSUsageTrackerRegisterCollectors {

	private const MEDIAWIKI_SIDEBAR = 'mediawiki-sidebar';

	protected function doProcess() {
		$activeSidebar = MediaWikiServices::getInstance()->getConfigFactory()
			->makeConfig( 'bsg' )->get( 'DiscoverySidebarPrimaryMainTabPanelMenu' );
		$isActive = ( $activeSidebar === self::MEDIAWIKI_SIDEBAR ) ? 1 : 0;

		$this->collectorConfig['mediawiki-sidebar-active'] = [
			'class' => 'Basic',
			'config' => [
				'identifier' => 'mediawiki-sidebar-active',
				'internalDesc' => 'Is the MediaWiki Sidebar active?',
				'count' => $isActive
			]
		];
	}
}
