<?php

namespace MediaWiki\Extension\PDFCreator\Factory;

use MediaWiki\Extension\PDFCreator\IStylesheetsProvider;
use MediaWiki\Extension\PDFCreator\Utility\ExportContext;
use MediaWiki\Registration\ExtensionRegistry;
use Wikimedia\ObjectFactory\ObjectFactory;

class StylesheetsFactory {

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
	public function getStylesheets( string $module, ExportContext $context ): array {
		$registry = ExtensionRegistry::getInstance()->getAttribute(
			'PDFCreatorStylesheetsProvider'
		);

		$stylesheets = [];
		foreach ( $registry as $spec ) {
			$provider = $this->objectFactory->createObject( $spec );
			if ( $provider instanceof IStylesheetsProvider === false ) {
				continue;
			}
			$styles = $provider->execute( $module, $context );
			$stylesheets = array_merge(
				$stylesheets,
				$styles
			);
		}

		return $stylesheets;
	}
}
