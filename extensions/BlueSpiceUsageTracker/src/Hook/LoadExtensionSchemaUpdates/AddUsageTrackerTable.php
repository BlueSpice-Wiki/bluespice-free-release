<?php

namespace BS\UsageTracker\Hook\LoadExtensionSchemaUpdates;

use BlueSpice\Hook\LoadExtensionSchemaUpdates;

class AddUsageTrackerTable extends LoadExtensionSchemaUpdates {

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		$dbType = $this->updater->getDB()->getType();
		$dir = $this->getExtensionPath();

		$this->updater->addExtensionTable(
			'bs_usagetracker',
			"$dir/maintenance/db/sql/$dbType/bs_usagetracker-generated.sql"
		);

		return true;
	}

	/**
	 *
	 * @return string
	 */
	protected function getExtensionPath() {
		return dirname( dirname( dirname( __DIR__ ) ) );
	}

}
