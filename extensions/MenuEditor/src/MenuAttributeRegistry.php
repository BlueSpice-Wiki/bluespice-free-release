<?php

namespace MediaWiki\Extension\MenuEditor;

use MWStake\MediaWiki\Component\ManifestRegistry\ManifestAttributeBasedRegistry;

/**
 * This class is only here because parent will only return last key of the value array
 */
class MenuAttributeRegistry extends ManifestAttributeBasedRegistry {
	/**
	 * @param string $key
	 * @param string $default
	 * @return callable|mixed|string
	 */
	public function getValue( $key, $default = '' ) {
		$registry = $this->getRegistryArray();
		return isset( $registry[$key] ) ? $registry[$key] : $default;
	}
}
