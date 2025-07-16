<?php

namespace MediaWiki\Extension\PDFCreator\Factory;

use MediaWiki\Extension\PDFCreator\IProcessor;
use MediaWiki\Registration\ExtensionRegistry;
use Wikimedia\ObjectFactory\ObjectFactory;

class ExportProcessorFactory {

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
	 * @return IProcessor[]
	 */
	public function getProcessors( string $module ): array {
		$registry = ExtensionRegistry::getInstance()->getAttribute(
			'PDFCreatorProcessors'
		);

		$processors = [];
		foreach ( $registry as $spec ) {
			$processor = $this->objectFactory->createObject( $spec );
			if ( $processor instanceof IProcessor ) {
				$processors[] = $processor;
			}
		}
		usort( $processors, static function ( IProcessor $a, IProcessor $b ) {
			$positionA = $a->getPosition();
			$positionB = $b->getPosition();

			return $positionA <=> $positionB;
		} );
		return $processors;
	}
}
