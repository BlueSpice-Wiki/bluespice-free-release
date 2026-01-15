<?php

namespace BlueSpice\InstanceStatus\InstanceStatusProvider;

use BlueSpice\InstanceStatus\IApiStatusProvider;
use MediaWiki\Http\HttpRequestFactory;
use MediaWiki\Message\Message;
use Psr\Log\NullLogger;

abstract class UrlReachableProvider implements IApiStatusProvider {

	/**
	 * @param HttpRequestFactory $httpRequestFactory
	 */
	public function __construct(
		private readonly HttpRequestFactory $httpRequestFactory
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function getValueForApi() {
		$url = $this->getUrl();
		if ( !$url ) {
			return 'URL not set';
		}
		$request = $this->httpRequestFactory->create( $url, [
			'http_errors' => false,
			'timeout' => 3.0,
		] );
		$request->setLogger( new NullLogger() );
		$status = $request->execute();

		if ( $status->isOK() ) {
			return 'OK';
		}

		return Message::newFromSpecifier( $status->getMessages()[0] )->text();
	}

	/**
	 * @return string
	 */
	abstract protected function getUrl(): string;
}
