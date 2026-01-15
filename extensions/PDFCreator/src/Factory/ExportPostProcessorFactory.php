<?php

namespace MediaWiki\Extension\PDFCreator\Factory;

use MediaWiki\Extension\PDFCreator\IPostProcessor;
use MediaWiki\Registration\ExtensionRegistry;
use Wikimedia\ObjectFactory\ObjectFactory;

class ExportPostProcessorFactory {

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
	 * @return IPostProcessor[]
	 */
	public function getProcessors( string $module ): array {
		$registry = ExtensionRegistry::getInstance()->getAttribute(
			'PDFCreatorPostProcessors'
		);

		$processors = [];
		foreach ( $registry as $spec ) {
			$processor = $this->objectFactory->createObject( $spec );
			if ( $processor instanceof IPostProcessor ) {
				$processors[] = $processor;
			}
		}
		return $processors;
	}
}
