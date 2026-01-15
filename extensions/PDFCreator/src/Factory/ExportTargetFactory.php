<?php

namespace MediaWiki\Extension\PDFCreator\Factory;

use MediaWiki\Config\Config;
use MediaWiki\Extension\PDFCreator\IExportTarget;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\Registration\ExtensionRegistry;
use Psr\Log\LoggerInterface;
use Wikimedia\ObjectFactory\ObjectFactory;

class ExportTargetFactory {

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
	 * @return IExportTarget|null
	 */
	public function getExportTarget( string $name = 'filesystem' ): ?IExportTarget {
		$provider = null;

		$registry = ExtensionRegistry::getInstance()->getAttribute(
			'PDFCreatorTargetProvider'
		);

		if ( $name === '' || !isset( $registry[$name] ) ) {
			$this->logger->error(
				"PDFCreator: Can not find injected export target provider named \"$name\"",
				[]
			);

			return null;
		}

		$spec = $registry[$name];
		$provider = $this->objectFactory->createObject( $spec, [] );

		if ( $provider instanceof IExportTarget === false ) {
			$this->logger->error(
				"PDFCreator: Export target provider named \"$name\" is not valid",
				[]
			);
			return null;
		}

		return $provider;
	}
}
