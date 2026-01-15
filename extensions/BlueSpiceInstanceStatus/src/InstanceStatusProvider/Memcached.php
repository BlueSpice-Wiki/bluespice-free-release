<?php

namespace BlueSpice\InstanceStatus\InstanceStatusProvider;

use BlueSpice\InstanceStatus\IApiStatusProvider;
use Config;
use ObjectCacheFactory;
use Wikimedia\ObjectCache\MemcachedBagOStuff;

class Memcached implements IApiStatusProvider {

	/** @var ObjectCacheFactory */
	private $objectCacheFactory;

	/** @var Config */
	private $config;

	/**
	 * @param ObjectCacheFactory $objectCacheFactory
	 * @param Config $config
	 */
	public function __construct( ObjectCacheFactory $objectCacheFactory, Config $config ) {
		$this->objectCacheFactory = $objectCacheFactory;
		$this->config = $config;
	}

	/**
	 * @inheritDoc
	 */
	public function getKeyForApi(): string {
		return 'ext-bluespicefoundation-memcached-connectivity';
	}

	/**
	 * @inheritDoc
	 */
	public function getValueForApi() {
		$cacheType = $this->config->get( 'MainCacheType' );
		if ( !in_array( $cacheType, [ CACHE_MEMCACHED, 'memcached-pecl' ] ) ) {
			return "wgMainCacheType set to $cacheType";
		}

		if ( empty( $this->config->get( 'MemCachedServers' ) ) ) {
			return "wgMemCachedServers empty";
		}

		$cache = $this->objectCacheFactory->getInstance( $cacheType );
		if ( !$cache instanceof MemcachedBagOStuff ) {
			return "Cache instance is not MemcachedBagOStuff, got " . get_class( $cache );
		}

		$value = $cache->getWithSetCallback(
			$cache->makeKey( 'memcached', 'healthcheck' ),
			$cache::TTL_PROC_SHORT,
			static function () {
				return 'OK';
			}
		);

		return $value === 'OK' ? 'OK' : "Cache returned value: $value";
	}
}
