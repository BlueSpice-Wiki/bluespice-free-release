<?php

namespace BlueSpice\Discovery\ConfigDefinition;

use BlueSpice\ConfigDefinition\ArraySetting;
use BlueSpice\Discovery\MenuSelectorOptions;
use MediaWiki\HTMLForm\Field\HTMLSelectField;

class SidebarPrimaryMainTabPanelMenu extends ArraySetting {

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
		return 'bs-discovery-config-sidebar-primary-main-tab-menu-label';
	}

	/**
	 * @return string
	 */
	public function getHelpMessageKey() {
		return 'bs-discovery-config-sidebar-primary-main-tab-menu-help';
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
	 * @return HTMLSelectField
	 */
	public function getHtmlFormField() {
		return new HTMLSelectField( $this->makeFormFieldParams() );
	}

	/**
	 *
	 * @return array
	 */
	protected function getOptions() {
		$menuSelectorOptions = new MenuSelectorOptions();
		return $menuSelectorOptions->getOptions();
	}
}
