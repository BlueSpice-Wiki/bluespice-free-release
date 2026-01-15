<?php

namespace MWStake\MediaWiki\Component\ManifestRegistry;

use MediaWiki\Config\GlobalVarConfig;
use MediaWiki\Registration\ExtensionRegistry;

class ManifestAttributeBasedRegistry implements IRegistry {

	public const OVERRIDE_SET = 'set';
	public const OVERRIDE_MERGE = 'merge';
	public const OVERRIDE_REMOVE = 'remove';

	/**
	 *
	 * @var string
	 */
	protected $attribName = '';

	/**
	 *
	 * @var ExtensionRegistry
	 */
	protected $extensionRegistry = null;

	/**
	 *
	 * @var array
	 */
	protected $overrides = [];

	/**
	 *
	 * @param string $attribName
	 * @param ExtensionRegistry|null $extensionRegistry
	 * @param array|null $overrides
	 */
	public function __construct( $attribName, $extensionRegistry = null, $overrides = null ) {
		$this->attribName = $attribName;
		$this->extensionRegistry = $extensionRegistry;

		if ( $this->extensionRegistry === null ) {
			$this->extensionRegistry = ExtensionRegistry::getInstance();
		}
		if ( $overrides === null ) {
			$config = new GlobalVarConfig( 'mwsgManifestRegistry' );
			$overrides = $config->get( 'Overrides' );

		}
		$this->overrides = [];
		if ( isset( $overrides[ $attribName ] ) ) {
			$this->overrides = $overrides[ $attribName ];
		}
	}

	/**
	 *
	 * @param string $key
	 * @param string $default
	 * @return string|callable
	 */
	public function getValue( $key, $default = '' ) {
		$registry = $this->getRegistryArray();
		$value = isset( $registry[$key] ) ? $registry[$key] : $default;

		if ( is_array( $value ) ) {
			// Attributes get merged together instead of being overwritten,
			// so just take the last one
			$value = end( $value );
		}

		return $value;
	}

	/**
	 *
	 * @return string[]
	 */
	public function getAllKeys() {
		$registry = $this->getRegistryArray();
		return array_keys( $registry );
	}

	/**
	 *
	 * @return array
	 */
	public function getAllValues() {
		$all = [];
		foreach ( $this->getAllKeys() as $key ) {
			$all[$key] = $this->getValue( $key );
		}
		return $all;
	}

	/**
	 *
	 * @return array
	 */
	protected function getRegistryArray() {
		$registry = $this->extensionRegistry->getAttribute( $this->attribName );
		if ( isset( $this->overrides[static::OVERRIDE_SET ] ) ) {
			$registry = $this->overrides[static::OVERRIDE_SET ];
		} else {
			if ( isset( $this->overrides[static::OVERRIDE_MERGE ] ) ) {
				$registry = array_merge(
					$registry,
					$this->overrides[static::OVERRIDE_MERGE ]
				);
			}
			if ( isset( $this->overrides[static::OVERRIDE_REMOVE ] ) ) {
				foreach ( $this->overrides[static::OVERRIDE_REMOVE ] as $removeKey ) {
					if ( isset( $registry[ $removeKey ] ) ) {
						unset( $registry[ $removeKey ] );
					}
				}
			}
		}

		return $registry;
	}

	/**
	 * @param string $key
	 * @return array
	 */
	public function getObjectSpec( $key ): array {
		$objectSpec = [];

		$registry = $this->getRegistryArray();
		$specs = isset( $registry[$key] ) ? $registry[$key] : [];

		foreach ( $specs as $name => $value ) {
			/**
			 * Attributes get merged together instead of being overwritten.
			 * This may result in an array for class or factory which is not allowed.
			 *
			 * Other specifications like services are an array. It is not possible to
			 * decide which of them belong to the original factory and which to the override.
			 */
			if ( ( $name === 'class' ) && is_array( $value ) ) {
				$objectSpec[$name] = end( $value );
				continue;
			}
			if ( ( $name === 'factory' ) && is_array( $value ) ) {
				$objectSpec[$name] = end( $value );
				continue;
			}
			$objectSpec[$name] = $value;
		}
		return $objectSpec;
	}

}
