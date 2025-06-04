<?php

namespace MediaWiki\Extension\PDFCreator\Factory;

use MediaWiki\Extension\PDFCreator\IMetaDataProvider;
use MediaWiki\Extension\PDFCreator\Utility\ExportContext;
use MediaWiki\Registration\ExtensionRegistry;
use Wikimedia\ObjectFactory\ObjectFactory;

class MetaDataFactory {

	/** @var ObjectFactory */
	private $objectFactory;

	/**
	 * @param ObjectFactory $objectFactory
	 */
	public function __construct( ObjectFactory $objectFactory ) {
		$this->objectFactory = $objectFactory;
	}

	/**
	 * @param string $module
	 * @param ExportContext $context
	 * @return array
	 */
	public function getMetaData( string $module, ExportContext $context ): array {
		$registry = ExtensionRegistry::getInstance()->getAttribute(
			'PDFCreatorMetaDataProvider'
		);

		$meta = [];
		foreach ( $registry as $name => $spec ) {
			$provider = $this->objectFactory->createObject( $spec );
			if ( $provider instanceof IMetaDataProvider === false ) {
				continue;
			}
			$providerMeta = $provider->execute( $module, $context );
			$meta = array_merge( $meta, $providerMeta );
		}

		return $meta;
	}
}
