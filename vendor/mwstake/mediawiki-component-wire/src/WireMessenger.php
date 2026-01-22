<?php

namespace MWStake\MediaWiki\Component\Wire;

use Config;
use MediaWiki\Http\HttpRequestFactory;
use MediaWiki\WikiMap\WikiMap;
use Psr\Log\LoggerInterface;

class WireMessenger {

	/**
	 * @param HttpRequestFactory $httpRequestFactory
	 * @param Config $wireConfig
	 * @param LoggerInterface $logger
	 * @param WireListenerRegistry $listenerRegistry
	 */
	public function __construct(
		private readonly HttpRequestFactory $httpRequestFactory,
		private readonly Config $wireConfig,
		private readonly LoggerInterface $logger,
		private readonly WireListenerRegistry $listenerRegistry
	) {
	}

	/**
	 * @param WireMessage $message
	 * @return void
	 */
	public function send( WireMessage $message ) {
		$this->sendToWire( $message );
		$this->sendToListeners( $message );
	}

	/**
	 * @param WireMessage $message
	 * @return void
	 */
	private function sendToWire( WireMessage $message ) {
		$data = $message->jsonSerialize();
		$data['_wiki'] = WikiMap::getCurrentWikiId();
		$body = json_encode( $data );

		$url = $this->wireConfig->get( 'Url' );
		if ( !$url ) {
			$this->logger->debug( 'Wire service URL is not configured' );
			return;
		}
		$url = rtrim( $url, '/' ) . '/message';
		$options = [
			'postData' => $body,
			'method' => 'POST',
		];
		if ( $this->wireConfig->get( 'AllowInsecureSSL' ) ) {
			$options['sslVerifyCert'] = false;
			$options['sslVerifyHost'] = false;
		}
		$request = $this->httpRequestFactory->create( $url, $options );

		$request->setHeader( 'Authorization', 'Bearer ' . $this->wireConfig->get( 'ApiKey' ) );
		$request->setHeader( 'Content-Type', 'application/json' );
		$request->execute();

		if ( $request->getStatus() !== 200 || empty( $request->getContent() ) ) {
			$this->logger->error( 'Failed to relay message to wire service' );
		}
	}

	/**
	 * @param WireMessage $message
	 * @return void
	 */
	private function sendToListeners( WireMessage $message ) {
		$listeners = $this->listenerRegistry->getListeners( $message->getChannel() );
		foreach ( $listeners as $listener ) {
			$listener->onWireMessage( $message );
		}
	}

}
