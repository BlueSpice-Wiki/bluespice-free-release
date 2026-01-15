<?php

namespace MWStake\MediaWiki\Component\DynamicFileDispatcher;

use MediaWiki\HookContainer\HookContainer;
use MediaWiki\Utils\UrlUtils;

class DynamicFileDispatcherFactory {

	/**
	 * @var IDynamicFileModule[]
	 */
	protected $modules = [];

	/**
	 * @var HookContainer
	 */
	protected HookContainer $hookContainer;

	/**
	 * @var UrlUtils
	 */
	protected UrlUtils $urlUtils;

	/**
	 * @param array $modules
	 * @param HookContainer $hookContainer
	 * @param UrlUtils $urlUtils
	 * @throws \MWException
	 */
	public function __construct( array $modules, HookContainer $hookContainer, UrlUtils $urlUtils ) {
		$this->hookContainer = $hookContainer;
		$this->urlUtils = $urlUtils;
		$this->setModules( $modules );
	}

	/**
	 * @param array $modules
	 * @return void
	 * @throws \MWException
	 */
	private function setModules( array $modules ) {
		$this->hookContainer->run( 'MWStakeDynamicFileDispatcherRegisterModule', [ &$modules ] );
		foreach ( $modules as $moduleName => $module ) {
			if ( !( $module instanceof IDynamicFileModule ) ) {
				throw new \MWException(
					"Module '$moduleName' is not an instance of IDynamicFileModule"
				);
			}
			$this->modules[$moduleName] = $module;
		}
	}

	/**
	 * @param string $name
	 * @return \MWStake\MediaWiki\Component\DynamicFileDispatcher\IDynamicFileModule|null
	 */
	public function getModule( string $name ): ?IDynamicFileModule {
		return $this->modules[$name] ?? null;
	}

	/**
	 * Get URL to retrieve dynamic file
	 * @param string $module
	 * @param array $params
	 * @return string
	 */
	public function getUrl( string $module, array $params ) {
		$url = $this->urlUtils->getServer( PROTO_CURRENT ) .
			wfScript( 'rest' ) .
			'/mws/v1/dynamic-file-dispatcher/' . $module;
		if ( !empty( $params ) ) {
			$url .= '?' . http_build_query( $params );
		}
		return $url;
	}
}
