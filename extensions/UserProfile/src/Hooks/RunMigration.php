<?php

namespace MediaWiki\Extension\UserProfile\Hooks;

use MediaWiki\Extension\UserProfile\Maintenance\MigrateSocialProfiles;
use MediaWiki\Installer\Hook\LoadExtensionSchemaUpdatesHook;

class RunMigration implements LoadExtensionSchemaUpdatesHook {

	/**
	 * @inheritDoc
	 */
	public function onLoadExtensionSchemaUpdates( $updater ) {
		$updater->addPostDatabaseUpdateMaintenance(
			MigrateSocialProfiles::class
		);
	}
}
