<?php

namespace MediaWiki\Extension\EnhancedUpload\HookHandler;

use MediaWiki\Installer\Hook\LoadExtensionSchemaUpdatesHook;

class RunDatabaseUpdates implements LoadExtensionSchemaUpdatesHook {

	/**
	 * @inheritDoc
	 */
	public function onLoadExtensionSchemaUpdates( $updater ) {
		$updater->addPostDatabaseUpdateMaintenance(
			'MediaWiki\Extension\EnhancedUpload\Migration\MigrateConfigSettings'
		);
	}

}
