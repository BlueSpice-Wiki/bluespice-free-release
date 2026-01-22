<?php

namespace MWStake\MediaWiki\Component\AlertBanners\AlertProviderFactory;

use MediaWiki\Config\Config;
use MediaWiki\Config\GlobalVarConfig;
use Wikimedia\ObjectFactory\ObjectFactory;

class GlobalVars extends Base {

	/** @var Config */
	private $config;

	/**
	 * @param ObjectFactory $objectFactory
	 * @param Config|null $config
	 */
	public function __construct( $objectFactory, $config = null ) {
		$this->config = $config;
		if ( $config === null ) {
			$this->config = new GlobalVarConfig( 'mwsgAlertBanners' );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function processProviders( $providers ) {
		$handlerSpecs = $this->config->get( 'ProviderRegistry' );
		foreach ( $handlerSpecs as $handlerId => $handlerSpec ) {
			$providers[$handlerId] = $this->objectFactory->createObject( $handlerSpec );
		}

		return $providers;
	}
}
