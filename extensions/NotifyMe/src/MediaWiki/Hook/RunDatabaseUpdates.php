<?php

namespace MediaWiki\Extension\NotifyMe\MediaWiki\Hook;

use MediaWiki\Extension\NotifyMe\MediaWiki\Maintenance\AddDefaultMailTemplates;
use MediaWiki\Installer\Hook\LoadExtensionSchemaUpdatesHook;

class RunDatabaseUpdates implements LoadExtensionSchemaUpdatesHook {

	/**
	 * @inheritDoc
	 */
	public function onLoadExtensionSchemaUpdates( $updater ) {
		$dbType = $updater->getDB()->getType();
		$dir = dirname( __DIR__, 3 );

		$updater->addExtensionTable(
			'notifications_event',
			"$dir/db/$dbType/notifications_event.sql"
		);
		$updater->addExtensionTable(
			'notifications_instance',
			"$dir/db/$dbType/notifications_instance.sql"
		);
		$updater->addExtensionTable(
			'notifications_web_query_store',
			"$dir/db/$dbType/notifications_web_query_store.sql"
		);

		$updater->dropExtensionIndex(
			'notifications_event',
			'notifications_event_key_timestamp',
			"$dir/db/$dbType/drop_event_index.sql"
		);

		$updater->addPostDatabaseUpdateMaintenance(
			AddDefaultMailTemplates::class
		);
		return true;
	}
}
