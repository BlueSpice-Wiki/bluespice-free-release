<?php

namespace BlueSpice\ExtendedStatistics\Hook\LoadExtensionSchemaUpdates;

use BlueSpice\Hook\LoadExtensionSchemaUpdates;
use MediaWiki\Installer\DatabaseUpdater;

class AddSnapshotTable extends LoadExtensionSchemaUpdates {

	protected function doProcess() {
		$dbType = $this->updater->getDB()->getType();
		$dir = $this->getExtensionPath();

		$this->updater->addExtensionTable(
			'bs_extendedstatistics_snapshot',
			"$dir/maintenance/db/sql/$dbType/bs_extendedstatistics_snapshot-generated.sql"
		);

		if ( $dbType == 'mysql' ) {
			$this->updater->addExtensionField(
				'bs_extendedstatistics_snapshot', 'ess_type',
				"$dir/maintenance/db/bs_extendedstatistics_snapshot.patch.add_type.sql"
			);

			$this->updater->addExtensionField(
				'bs_extendedstatistics_snapshot', 'ess_interval',
				"$dir/maintenance/db/bs_extendedstatistics_snapshot.patch.add_interval.sql"
			);

			$this->updater->addExtensionField(
				'bs_extendedstatistics_snapshot', 'ess_secondary_data',
				"$dir/maintenance/db/bs_extendedstatistics_snapshot.patch.add_secondary_data.sql"
			);

			$this->updater->dropExtensionField(
				'bs_extendedstatistics_snapshot', 'ess_id',
				"$dir/maintenance/db/bs_extendedstatistics_snapshot.patch.remove_id.sql"
			);
			$this->updater->modifyExtensionField(
				'bs_extendedstatistics_snapshot', 'ess_data',
				"$dir/maintenance/db/bs_extendedstatistics_snapshot.patch.modify_data.sql"
			);
		}

		$this->updater->addExtensionUpdate( [ [ $this, 'removeOldData' ] ] );
	}

	/**
	 *
	 * @return string
	 */
	protected function getExtensionPath() {
		return dirname( dirname( dirname( __DIR__ ) ) );
	}

	/**
	 * @param DatabaseUpdater $updater
	 */
	public function removeOldData( DatabaseUpdater $updater ) {
		$db = $updater->getDB();
		if (
			!$db->fieldExists(
				'bs_extendedstatistics_snapshot',
				'ess_type',
				__METHOD__
			)
		) {
			$updater->output( 'Field ess_type does not exists, aborting' );
			return;
		}
		$db->delete(
			'bs_extendedstatistics_snapshot',
			[
				'ess_type IS NULL'
			],
			__METHOD__
		);

		$updater->output( 'Cleared all old data' );
	}
}
