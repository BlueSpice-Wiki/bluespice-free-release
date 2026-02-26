<?php

namespace BlueSpice\PageAssignments\HookHandler\NamespaceManagerCollectNamespaceProperties;

class AddNamespaceProperties {

	/**
	 * @inheritDoc
	 */
	public function onNamespaceManagerCollectNamespaceProperties(
		int $namespaceId,
		array $globals,
		array &$properties
	): void {
		$properties['pageassignments-secure'] = in_array(
			$namespaceId,
			$globals['bsgPageAssignmentsSecureEnabledNamespaces'] ?? []
		);
	}

}
