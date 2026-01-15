<?php

/**
 * Called via commandline
 * Can be run without params
 * Registers usage statistics collect jobs with the job queue. In order to
 * actually get the data, you need to execute maintenance/runJobs.php in
 * in addition. Typical commandline:
 * ?> php extensions/BlueSpiceExtensions/UsageTracker/maintenance/usagetrackerUpdate.php
 * ?> php maintenance/runJobs.php
 * runJobs, however, should be run on a cronjob anyways.
 */

// We are on <mediawiki>/extensions/BlueSpiceUsageTracker/maintenance
$IP = realpath( dirname( dirname( __DIR__ ) ) );

require_once $IP . '/BlueSpiceFoundation/maintenance/BSMaintenance.php';

use MediaWiki\MediaWikiServices;
use MediaWiki\Settings\SettingsBuilder;

class UsageTrackerUpdate extends BSMaintenance {
	public function __construct() {
		parent::__construct();
		$this->requireExtension( 'BlueSpiceUsageTracker' );
	}

	public function execute() {
		$em = MediaWikiServices::getInstance()->getService( 'BSExtensionFactory' );
		$aData = $em->getExtension( 'BlueSpiceUsageTracker' )->getUsageData();
	}

	/**
	 * @inheritDoc
	 */
	public function finalSetup( ?SettingsBuilder $settingsBuilder = null ) {
		// @phan-suppress-next-line PhanParamTooMany temporary, see gerrit 757469
		parent::finalSetup( $settingsBuilder );
		$GLOBALS['wgMainCacheType'] = CACHE_NONE;
	}
}

$maintClass = UsageTrackerUpdate::class;
require_once RUN_MAINTENANCE_IF_MAIN;
