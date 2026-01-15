<?php

namespace BlueSpice\VisualEditorConnector\Hook;

use BlueSpice\NamespaceManager\Hook\NamespaceManagerBeforePersistSettingsHook;

class WriteNamespaceConfiguration implements NamespaceManagerBeforePersistSettingsHook {

	/**
	 * @inheritDoc
	 */
	public function onNamespaceManagerBeforePersistSettings(
		array &$configuration, int $id, array $definition, array $mwGlobals
	): void {
		$enabledNamespaces = $mwGlobals['wgVisualEditorAvailableNamespaces'] ?? [];
		$currentlyActivated = false;
		if ( isset( $enabledNamespaces[$id] ) && $enabledNamespaces[$id] === true ) {
			$currentlyActivated = true;
		}

		$explicitlyDeactivated = false;
		if ( isset( $definition['visualeditor'] ) && $definition['visualeditor'] === false ) {
			$explicitlyDeactivated = true;
		}

		$explicitlyActivated = false;
		if ( isset( $definition['visualeditor'] ) && $definition['visualeditor'] === true ) {
			$explicitlyActivated = true;
		}

		if ( ( $currentlyActivated && !$explicitlyDeactivated ) || $explicitlyActivated ) {
			$configuration['wgVisualEditorAvailableNamespaces'][$id] = true;
		} else {
			$configuration['wgVisualEditorAvailableNamespaces'][$id] = false;
		}
	}
}
