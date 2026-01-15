<?php

namespace MWStake\MediaWiki\Component\ManifestRegistry;

interface IRegistry {

	/**
	 *
	 * @param string $key
	 * @param string $default
	 * @return string
	 */
	public function getValue( $key, $default = '' );

	/**
	 * @return string[]
	 */
	public function getAllKeys();
}
