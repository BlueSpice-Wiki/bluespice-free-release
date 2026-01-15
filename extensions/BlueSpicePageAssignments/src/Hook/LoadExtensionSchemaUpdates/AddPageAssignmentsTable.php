<?php

namespace BlueSpice\PageAssignments\Hook\LoadExtensionSchemaUpdates;

use BlueSpice\Hook\LoadExtensionSchemaUpdates;

class AddPageAssignmentsTable extends LoadExtensionSchemaUpdates {

	protected function doProcess() {
		$dbType = $this->updater->getDB()->getType();
		$dir = $this->getExtensionPath();

		$this->updater->addExtensionTable(
			'bs_pageassignments',
			"$dir/maintenance/db/sql/$dbType/bs_pageassignments-generated.sql"
		);

		if ( $dbType == 'mysql' ) {
			$this->updater->modifyExtensionField(
				'bs_pageassignments',
				'pa_page_id',
				"$dir/maintenance/db/ps_pageassignments.primary_key.patch.sql"
			);
		}
	}

	/**
	 *
	 * @return string
	 */
	protected function getExtensionPath() {
		return dirname( dirname( dirname( __DIR__ ) ) );
	}
}
