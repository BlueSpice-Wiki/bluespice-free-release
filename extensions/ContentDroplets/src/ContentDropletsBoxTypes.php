<?php

namespace MediaWiki\Extension\ContentDroplets;

use MWStake\MediaWiki\Component\ManifestRegistry\ManifestAttributeBasedRegistry;

class ContentDropletsBoxTypes {

	/**
	 *
	 * @return array
	 */
	public static function getRegisteredBoxTypes() {
		$registry = new ManifestAttributeBasedRegistry(
			'ContentDropletsDroplets'
		);

		$textBoxTypes = [];
		foreach ( $registry->getAllKeys() as $key ) {
			if ( str_contains( $key, 'text-box-' ) ) {
				$type = str_replace( 'text-box-', '', $key );
				$textBoxTypes[] = $type;
			}
		}
		return $textBoxTypes;
	}
}
