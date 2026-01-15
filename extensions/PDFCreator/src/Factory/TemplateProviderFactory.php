<?php

namespace MediaWiki\Extension\PDFCreator\Factory;

use MediaWiki\Config\Config;
use MediaWiki\Extension\PDFCreator\ITemplateProvider;
use MediaWiki\Registration\ExtensionRegistry;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Wikimedia\ObjectFactory\ObjectFactory;

class TemplateProviderFactory implements LoggerAwareInterface {

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
		$this->config = $config;
		$this->objectFactory = $objectFactory;
	}

	/**
	 * @param string $name
	 * @return ITemplateProvider|null
	 */
	public function getTemplateProvider( string $name = '' ): ?ITemplateProvider {
		$registry = $this->getTemplateProviderRegistry();

		// TODO: Is PDFCreatorTemplateProvider requried?
		if ( $name === '' ) {
			// use configured template
			$name = $this->config->get( 'PDFCreatorTemplateProvider' );
			if ( !isset( $registry[$name] ) ) {
				$this->logger->error(
					"PDFCreator: Can not find configured template provider named \"$name\"",
					[]
				);
				return null;
			}
		} else {
			// use injected template
			if ( !isset( $registry[$name] ) ) {
				$this->logger->error(
					"PDFCreator: Can not find injected template provider named \"$name\"",
					[]
				);

				// fallback to configured template
				$name = $this->config->get( 'PDFCreatorTemplateProvider' );
				if ( !isset( $registry[$name] ) ) {
					$this->logger->error(
						"PDFCreator: Can not find configured template provider named \"$name\"",
						[]
					);
					return null;
				}
			}
		}

		$spec = $registry[$name];
		$provider = $this->objectFactory->createObject( $spec, [] );

		if ( $provider instanceof ITemplateProvider === false ) {
			$this->logger->error(
				"PDFCreator: Template provider named \"$name\" is not valid",
				[]
			);
			return null;
		}

		return $provider;
	}

	/**
	 * @param string $templateName
	 * @return ITemplateProvider|null
	 */
	public function getTemplateProviderFor( string $templateName = '' ): ?ITemplateProvider {
		$registry = $this->getTemplateProviderRegistry();
		foreach ( $registry as $spec ) {
			$provider = $this->objectFactory->createObject( $spec, [] );
			if ( $provider instanceof ITemplateProvider === false ) {
				continue;
			}
			$templateNames = $provider->getTemplateNames();
			if ( in_array( $templateName, $templateNames ) ) {
				return $provider;
			}
		}

		return null;
	}

	/**
	 * @return array
	 */
	public function getAvailableProviderTemplateNames(): array {
		$templateNames = [];

		$registry = $this->getTemplateProviderRegistry();
		foreach ( $registry as $key => $spec ) {
			$provider = $this->objectFactory->createObject( $spec, [] );
			if ( $provider instanceof ITemplateProvider === false ) {
				continue;
			}
			$templateNames[ $key ] = $provider->getTemplateNames();
		}

		return $templateNames;
	}

	/**
	 * @return array
	 */
	public function getAvailableTemplateNames(): array {
		$templateNames = [];

		$registry = $this->getTemplateProviderRegistry();
		foreach ( $registry as $spec ) {
			$provider = $this->objectFactory->createObject( $spec, [] );
			if ( $provider instanceof ITemplateProvider === false ) {
				continue;
			}
			$templateNames = array_merge(
				$templateNames,
				$provider->getTemplateNames()
			);
		}

		return array_unique( $templateNames );
	}

	/**
	 * @return array
	 */
	private function getTemplateProviderRegistry(): array {
		$registry = ExtensionRegistry::getInstance()->getAttribute(
			'PDFCreatorTemplateProvider'
		);

		return $registry;
	}

	/**
	 * @param LoggerInterface $logger
	 * @return void
	 */
	public function setLogger( LoggerInterface $logger ) {
		$this->logger = $logger;
	}
}
