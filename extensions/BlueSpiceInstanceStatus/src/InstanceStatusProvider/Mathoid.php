<?php

namespace BlueSpice\InstanceStatus\InstanceStatusProvider;

use Config;
use MediaWiki\Http\HttpRequestFactory;

class Mathoid extends UrlReachableProvider {

	/**
	 * @param Config $config
	 * @param HttpRequestFactory $httpRequestFactory
	 */
	public function __construct(
		private readonly Config $config,
		private readonly HttpRequestFactory $httpRequestFactory
	) {
		parent::__construct( $this->httpRequestFactory );
	}

	/**
	 * @inheritDoc
	 */
	public function getKeyForApi(): string {
		return 'mathoid-backend-connectivity';
	}

	/**
	 * @return string
	 */
	protected function getUrl(): string {
		return $this->config->get( 'MathMathMLUrl' );
	}
}
