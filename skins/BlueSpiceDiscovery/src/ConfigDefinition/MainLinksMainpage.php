<?php

namespace BlueSpice\Discovery\ConfigDefinition;

use BlueSpice\ConfigDefinition\BooleanSetting;

class MainLinksMainpage extends BooleanSetting {

	/**
	 * @return array
	 */
	public function getPaths() {
		return [
			static::MAIN_PATH_FEATURE . '/' . static::FEATURE_SKINNING . '/BlueSpiceDiscovery',
			static::MAIN_PATH_EXTENSION . '/BlueSpiceDiscovery/' . static::FEATURE_SKINNING,
			static::MAIN_PATH_PACKAGE . '/' . static::PACKAGE_FREE . '/BlueSpiceDiscovery',
		];
	}

	/**
	 * @return string
	 */
	public function getLabelMessageKey() {
		return 'bs-discovery-config-mainlinks-mainpage-label';
	}

	/**
	 * @return string
	 */
	public function getHelpMessageKey() {
		return 'bs-discovery-config-mainlinks-mainpage-help';
	}

}
