<?php

namespace MediaWiki\Extension\PDFCreator\Factory;

use MediaWiki\Config\Config;
use MediaWiki\Extension\PDFCreator\IExportBackend;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\Registration\ExtensionRegistry;
use Psr\Log\LoggerInterface;
use Wikimedia\ObjectFactory\ObjectFactory;

class ExportBackendFactory {

	/** @var LoggerInterface */
	private $logger;

	/** @var Config */
	private $config;

	/** @var ObjectFactory */
	private $objectFactory;

	/**
	 * @param Config $config
	 * @param ObjectFactory $objectFactory
	 */
	public function __construct( Config $config, ObjectFactory $objectFactory ) {
		$this->logger = LoggerFactory::getInstance( 'PDFCreator' );
		$this->config = $config;
		$this->objectFactory = $objectFactory;
	}

	/**
	 * @param string $name
	 * @return IExportBackend|null
	 */
	public function getBackend( string $name = '' ): ?IExportBackend {
		$registry = ExtensionRegistry::getInstance()->getAttribute(
			'PDFCreatorBackendProvider'
		);

		if ( $name === '' ) {
			$names = array_keys( $registry );
			if ( !empty( $names ) ) {
				$name = $names[0];
			}
		}

		if ( $name === '' || !isset( $registry[$name] ) ) {
			$this->logger->error(
				"PDFCreator: Can not find injected export backend provider named \"$name\"",
				[]
			);

			return null;
		}

		$spec = $registry[$name];
		$backend = $this->objectFactory->createObject( $spec, [] );

		if ( $backend instanceof IExportBackend === false ) {
			$this->logger->error(
				"PDFCreator: Export backend provider named \"$name\" is not valid",
				[]
			);
			return null;
		}

		return $backend;
	}
}
