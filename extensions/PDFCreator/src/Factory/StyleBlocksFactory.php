<?php

namespace MediaWiki\Extension\PDFCreator\Factory;

use MediaWiki\Extension\PDFCreator\IStyleBlocksProvider;
use MediaWiki\Extension\PDFCreator\Utility\ExportContext;
use MediaWiki\Registration\ExtensionRegistry;
use Wikimedia\ObjectFactory\ObjectFactory;

class StyleBlocksFactory {

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
	public function getStyleBlocks( string $module, ExportContext $context ): array {
		$registry = ExtensionRegistry::getInstance()->getAttribute(
			'PDFCreatorStyleBlocksProvider'
		);

		$StyleBlocks = [];
		foreach ( $registry as $spec ) {
			$provider = $this->objectFactory->createObject( $spec );
			if ( $provider instanceof IStyleBlocksProvider === false ) {
				continue;
			}
			$styles = $provider->execute( $module, $context );
			$StyleBlocks = array_merge(
				$StyleBlocks,
				$styles
			);
		}

		return $StyleBlocks;
	}
}
