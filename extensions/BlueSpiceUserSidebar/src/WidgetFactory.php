<?php

namespace BlueSpice\UserSidebar;

use MediaWiki\Config\Config;
use MediaWiki\Context\IContextSource;
use MWStake\MediaWiki\Component\ManifestRegistry\IRegistry;

class WidgetFactory {

	/**
	 *
	 * @var IRegistry
	 */
	private $registry = null;

	/**
	 *
	 * @var Config
	 */
	private $config = null;

	/**
	 *
	 * @param IRegistry $registry
	 * @param Config $config
	 */
	public function __construct( IRegistry $registry, Config $config ) {
		$this->registry = $registry;
		$this->config = $config;
	}

	/**
	 * @param IContextSource $context
	 * @param array $params
	 * @return IWidget[]
	 */
	public function getAll( IContextSource $context, array $params = [] ) {
		$instances = [];
		foreach ( $this->registry->getAllKeys() as $key ) {
			$callback = $this->registry->getValue( $key );
			if ( !is_callable( $callback ) ) {
				continue;
			}
			$instance = call_user_func_array(
				$callback,
				[ $key, $context, $this->config, $params ]
			);
			if ( !$instance instanceof IWidget ) {
				continue;
			}
			$instances[$key] = $instance;
		}

		return $instances;
	}

	/**
	 *
	 * @param string $key
	 * @param IContextSource $context
	 * @param array $params
	 * @return IWidget|null
	 */
	public function get( $key, IContextSource $context, array $params = [] ) {
		$instances = $this->getAll( $context, $params );
		return isset( $instances[$key] ) ? $instances[$key] : null;
	}

	/**
	 *
	 * @return string[]
	 */
	public function getAllKeys(): array {
		return $this->registry->getAllKeys();
	}
}
