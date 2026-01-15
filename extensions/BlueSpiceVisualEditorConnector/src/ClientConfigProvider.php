<?php

namespace BlueSpice\VisualEditorConnector;

use BlueSpice\VisualEditorConnector\ClientConfig\IVisualEditorConnectorClientConfigVariable;
use MWStake\MediaWiki\Component\ManifestRegistry\ManifestAttributeBasedRegistry;

class ClientConfigProvider {

	/**
	 * For use in `extension.json`
	 *
	 * @return array
	 */
	public static function packageFilesCallback(): array {
		$configProvider = new self();
		return $configProvider->getConfig();
	}

	/**
	 * @return array
	 */
	public function getConfig(): array {
		$config = [];
		$registry = new ManifestAttributeBasedRegistry( 'BlueSpiceVisualEditorConnectorClientConfig' );
		$entryKeys = $registry->getAllKeys();
		foreach ( $entryKeys as $entryKey ) {
			$callback = $registry->getValue( $entryKey );
			/** @var IVisualEditorConnectorClientConfigVariable */
			$configObj = call_user_func( $callback );
			$config[$entryKey] = $configObj->getValue();
		}

		return $config;
	}
}
