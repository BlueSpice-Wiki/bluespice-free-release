<?php

namespace MediaWiki\Extension\PDFCreator\Factory;

use MediaWiki\Extension\PDFCreator\IHtmlProvider;
use MediaWiki\Registration\ExtensionRegistry;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Wikimedia\ObjectFactory\ObjectFactory;

class HtmlProviderFactory implements LoggerAwareInterface {

	/** @var LoggerInterface */
	private $logger;

	/** @var ObjectFactory */
	private $objectFactory;

	/**
	 * @param ObjectFactory $objectFactory
	 */
	public function __construct( ObjectFactory $objectFactory ) {
		$this->objectFactory = $objectFactory;
	}

	/**
	 * @param string $key
	 * @return IHtmlProvider|null
	 */
	public function getProvider( string $key ): ?IHtmlProvider {
		$registry = ExtensionRegistry::getInstance()->getAttribute(
			'PDFCreatorHtmlProvider'
		);

		$provider = null;
		if ( isset( $registry[$key] ) ) {
			$provider = $this->objectFactory->createObject( $registry[$key] );

		} else {
			if ( $this->logger instanceof LoggerInterface ) {
				$this->logger->error( "PDFCreator HtmlProviderFactory not valid HtmlProvider for key $key" );
			}
		}

		return $provider;
	}

	/**
	 * @param LoggerInterface $logger
	 * @return void
	 */
	public function setLogger( LoggerInterface $logger ): void {
		$this->logger = $logger;
	}
}
