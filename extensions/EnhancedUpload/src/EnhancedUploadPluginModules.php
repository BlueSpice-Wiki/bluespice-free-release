<?php

namespace MediaWiki\Extension\EnhancedUpload;

use MWStake\MediaWiki\Component\ManifestRegistry\ManifestAttributeBasedRegistry;

class EnhancedUploadPluginModules {

	/**
	 *
	 * @return void
	 */
	public static function getPluginModules() {
		$registry = new ManifestAttributeBasedRegistry(
			'EnhancedUploadUploadFormPluginModules'
		);

		$pluginModules = [];
		foreach ( $registry->getAllKeys() as $key ) {
			$moduleName = $registry->getValue( $key );
			$pluginModules[] = $moduleName;
		}

		return $pluginModules;
	}
}
