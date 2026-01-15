<?php

namespace BlueSpice\VisualEditorConnector\ClientConfig;

use BlueSpice\ExtensionAttributeBasedRegistry;

class PluginModules extends ConfigBase {

	/**
	 * @inheritDoc
	 */
	public function getValue() {
		$registry = new ExtensionAttributeBasedRegistry( $this->getAttribute() );

		$pluginModules = [];
		foreach ( $registry->getAllKeys() as $key ) {
			$moduleName = $registry->getValue( $key );
			$pluginModules[] = $moduleName;
		}

		return $pluginModules;
	}

	/**
	 * @return string
	 */
	protected function getAttribute() {
		return 'BlueSpiceVisualEditorConnectorPluginModules';
	}
}
