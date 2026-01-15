<?php

namespace MWStake\MediaWiki\Component\ManifestRegistry;

use MediaWiki\Registration\ExtensionRegistry;

class ManifestRegistryFactory {

	/**
	 *
	 * @var ExtensionRegistry
	 */
	private $extensionRegistry = null;

	/**
	 *
	 * @var array
	 */
	private $overrides = [];

	/**
	 *
	 * @param ExtensionRegistry $extensionRegistry
	 * @param array $overrides
	 */
	public function __construct( $extensionRegistry, $overrides ) {
		$this->extensionRegistry = $extensionRegistry;
		$this->overrides = $overrides;
	}

	/**
	 *
	 * @param string $manifestAttributeKey
	 * @return IRegistry
	 */
	public function get( $manifestAttributeKey ): IRegistry {
		$registry = new ManifestAttributeBasedRegistry(
			$manifestAttributeKey,
			$this->extensionRegistry,
			$this->overrides
		);

		return $registry;
	}
}
