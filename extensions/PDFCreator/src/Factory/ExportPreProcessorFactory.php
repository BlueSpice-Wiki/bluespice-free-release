<?php

namespace MediaWiki\Extension\PDFCreator\Factory;

use MediaWiki\Extension\PDFCreator\IPreProcessor;
use MediaWiki\Registration\ExtensionRegistry;
use Wikimedia\ObjectFactory\ObjectFactory;

class ExportPreProcessorFactory {

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
	 * @return array
	 */
	public function getProcessors( string $module ): array {
		$registry = ExtensionRegistry::getInstance()->getAttribute(
			'PDFCreatorPreProcessors'
		);

		$processors = [];
		foreach ( $registry as $spec ) {
			$processor = $this->objectFactory->createObject( $spec );
			if ( $processor instanceof IPreProcessor ) {
				$processors[] = $processor;
			}
		}
		return $processors;
	}
}
