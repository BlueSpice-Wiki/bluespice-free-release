<?php

namespace BlueSpice\VisualEditorConnector\ClientConfig;

use BlueSpice\ExtensionAttributeBasedRegistry;

class TagDefinitions extends ConfigBase {

	/**
	 * @inheritDoc
	 */
	public function getValue() {
		$tagRegistry = new ExtensionAttributeBasedRegistry(
			'BlueSpiceVisualEditorConnectorTagDefinitions'
		);

		return $tagRegistry->getAllValues();
	}
}
