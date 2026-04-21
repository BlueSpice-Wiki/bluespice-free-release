<?php

namespace BlueSpice\WhoIsOnline\Hook\LoadExtensionSchemaUpdates;

use MediaWiki\Installer\Hook\LoadExtensionSchemaUpdatesHook;

class AddWhoIsOnlineTable implements LoadExtensionSchemaUpdatesHook {

	/**
	 * @inheritDoc
	 */
	public function onLoadExtensionSchemaUpdates( $updater ) {
		$dbType = $updater->getDB()->getType();
		$dir = dirname( __DIR__, 3 );

		$updater->addExtensionTable(
			'bs_whoisonline',
			"$dir/maintenance/db/$dbType/bs_whoisonline.sql"
		);

		if ( $dbType === 'mysql' ) {
			$updater->addExtensionField(
				'bs_whoisonline',
				'wo_action',
				"$dir/maintenance/db/bs_whoisonline.patch.wo_action.sql"
			);

			$updater->dropExtensionField(
				'bs_whoisonline',
				'wo_timestamp',
				"$dir/maintenance/db/bs_whoisonline.patch.wo_timestamp.sql"
			);

			$updater->addExtensionField(
				'bs_whoisonline',
				'wo_log_ts',
				"$dir/maintenance/db/bs_whoisonline.patch.wo_log_ts.sql"
			);

			$updater->addExtensionIndex(
				'bs_whoisonline',
				'wo_user_id',
				"$dir/maintenance/db/bs_whoisonline.patch.wo_user_id.index.sql"
			);

			$updater->addExtensionIndex(
				'bs_whoisonline',
				'wo_page_namespace',
				"$dir/maintenance/db/bs_whoisonline.patch.wo_page_namespace.index.sql"
			);

			$updater->addExtensionIndex(
				'bs_whoisonline',
				'wo_log_ts',
				"$dir/maintenance/db/bs_whoisonline.patch.wo_log_ts.index.sql"
			);
		}
	}

}
