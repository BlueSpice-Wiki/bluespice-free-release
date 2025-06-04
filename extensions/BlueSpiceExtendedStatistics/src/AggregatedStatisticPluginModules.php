<?php

namespace BlueSpice\ExtendedStatistics;

use MWStake\MediaWiki\Component\ManifestRegistry\ManifestAttributeBasedRegistry;

class AggregatedStatisticPluginModules {

	/**
	 *
	 * @return void
	 */
	public static function getPluginModules() {
		$registry = new ManifestAttributeBasedRegistry(
			'BlueSpiceExtendedStatisticsPluginModules'
		);

		$pluginModules = [];
		foreach ( $registry->getAllKeys() as $key ) {
			$moduleName = $registry->getValue( $key );
			$pluginModules[] = $moduleName;
		}

		return $pluginModules;
	}
}
