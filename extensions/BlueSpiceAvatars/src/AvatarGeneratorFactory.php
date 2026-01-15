<?php

namespace BlueSpice\Avatars;

use BlueSpice\ExtensionAttributeBasedRegistry;
use MediaWiki\Config\Config;

class AvatarGeneratorFactory {

	/**
	 *
	 * @var Config
	 */
	protected $config = null;

	/**
	 *
	 * @var ExtensionAttributeBasedRegistry
	 */
	protected $registry = null;

	/**
	 *
	 * @param Config $config
	 * @param ExtensionAttributeBasedRegistry $registry
	 */
	public function __construct( Config $config, ExtensionAttributeBasedRegistry $registry ) {
		$this->config = $config;
		$this->registry = $registry;
	}

	/**
	 *
	 * @param string $name
	 * @return IAvatarGenerator
	 */
	public function newFromName( $name ) {
		$callable = $this->registry->getValue( $name, null );
		if ( !$callable || !is_callable( $callable ) ) {
			return null;
		}
		$instance = call_user_func_array( $callable, [
			$name,
			$this->config
		] );
		return $instance;
	}

	/**
	 *
	 * @return IAvatarGenerator[]
	 */
	public function getAllGenerators() {
		$generators = [];
		foreach ( $this->registry->getAllKeys() as $name ) {
			$instance = $this->newFromName( $name );
			if ( !$instance ) {
				continue;
			}
			$generators[$name] = $instance;
		}
		return $generators;
	}
}
