<?php

namespace BlueSpice\Discovery\ConfigDefinition;

use BlueSpice\ConfigDefinition\ArraySetting;
use BlueSpice\Discovery\MetaItemOptions;
use MediaWiki\MediaWikiServices;

class MetaItemsFooter extends ArraySetting {

	/**
	 *
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
		return 'bs-discovery-config-meta-items-footer-label';
	}

	/**
	 * @return string
	 */
	public function getHelpMessageKey() {
		return 'bs-discovery-config-meta-items-footer-help';
	}

	/**
	 *
	 * @return string
	 */
	public function getVariableName() {
		return 'bsg' . $this->getName();
	}

	/**
	 *
	 * @return array
	 */
	protected function getOptions() {
		$services = MediaWikiServices::getInstance();
		$metaItemFactory = $services->getService( 'BlueSpiceDiscoveryMetaItemFactory' );
		$metaOptions = new MetaItemOptions( $metaItemFactory );
		return $metaOptions->getOptions();
	}

}
