<?php

namespace BlueSpice\InstanceStatus;

use MWStake\MediaWiki\Component\ManifestRegistry\ManifestAttributeBasedRegistry;

class InstanceStatusPluginModules {
	/**
	 *
	 * @return array
	 */
	public static function getPluginModules() {
		$registry = new ManifestAttributeBasedRegistry(
			'BlueSpiceInstanceStatusPluginModules'
		);
		$pluginModules = [];
		foreach ( $registry->getAllKeys() as $key ) {
			$moduleName = $registry->getValue( $key );
			$pluginModules[] = $moduleName;
		}
		return $pluginModules;
	}
}
