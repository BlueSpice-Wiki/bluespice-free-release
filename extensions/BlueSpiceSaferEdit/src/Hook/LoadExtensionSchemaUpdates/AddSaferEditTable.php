<?php

namespace BlueSpice\SaferEdit\Hook\LoadExtensionSchemaUpdates;

use BlueSpice\Hook\LoadExtensionSchemaUpdates;

class AddSaferEditTable extends LoadExtensionSchemaUpdates {

	protected function doProcess() {
		$dbType = $this->updater->getDB()->getType();
		$dir = $this->getExtensionPath();

		$this->updater->addExtensionTable(
			'bs_saferedit',
			"$dir/maintenance/db/sql/$dbType/bs_saferedit-generated.sql"
		);

		if ( $dbType == 'mysql' ) {
			$this->updater->addExtensionIndex(
				'bs_saferedit',
				'se_page_title',
				"$dir/maintenance/db/bs_saferedit.patch.se_page_title.index.sql"
			);

			$this->updater->addExtensionIndex(
				'bs_saferedit',
				'se_page_namespace',
				"$dir/maintenance/db/bs_saferedit.patch.se_page_namespace.index.sql"
			);

			$this->updater->dropExtensionField(
				'bs_saferedit',
				'se_text',
				"$dir/maintenance/db/bs_saferedit.patch.se_text.sql"
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
