<?php

namespace BlueSpice\Discovery;

use BlueSpice\Discovery\AttenstionIndicator\NULLIndicator;
use MediaWiki\Config\Config;
use MediaWiki\MediaWikiServices;
use MediaWiki\User\User;
use MWStake\MediaWiki\Component\ManifestRegistry\IRegistry;

class AttentionIndicatorFactory {
	/**
	 * @var IRegistry
	 */
	protected $registry = null;

	/**
	 * @var Config
	 */
	protected $config = null;

	/**
	 * @var MediaWikiServices
	 */
	protected $services = null;

	/**
	 * @var array
	 */
	protected $instances = [];

	/**
	 * @param IRegistry $registry
	 * @param Config $config
	 * @param MediaWikiServices $services
	 */
	public function __construct( IRegistry $registry, Config $config, MediaWikiServices $services ) {
		$this->registry = $registry;
		$this->config = $config;
		$this->services = $services;
	}

	/**
	 * @param User $user
	 * @return IAttentionIndicator[]
	 */
	public function getAll( User $user ): array {
		if ( $user->isAnon() ) {
			return [];
		}
		if ( isset( $this->instances[$user->getId()] ) ) {
			return $this->instances[$user->getId()];
		}
		$this->instances[$user->getId()] = [];
		foreach ( $this->registry->getAllKeys() as $key ) {
			$callback = $this->registry->getValue(
				$key,
				$this->getNUllIndicatorCallback()
			);
			if ( !is_callable( $callback ) ) {
				$callback = $this->getNUllIndicatorCallback();
			}
			$instance = call_user_func_array(
				$callback,
				[ $key, $this->config, $user, $this->services, ]
			);
			$this->instances[$user->getId()][$key] = $instance;
		}

		return $this->instances[$user->getId()];
	}

	/**
	 * @param string $key
	 * @param User $user
	 * @return IAttentionIndicator|NULLIndicator
	 */
	public function get( $key, User $user ): IAttentionIndicator {
		if ( isset( $this->getAll( $user )[$key] ) ) {
			return $this->getAll( $user )[$key];
		}
		return call_user_func_array(
			$this->getNUllIndicatorCallback(),
			[ $key, $this->config, $user, $this->services ]
		);
	}

	/**
	 *
	 * @return string
	 */
	protected function getNUllIndicatorCallback(): string {
		return "\\BlueSpice\\Discovery\\AttentionIndicator\\NULLIndicator::factory";
	}

}
