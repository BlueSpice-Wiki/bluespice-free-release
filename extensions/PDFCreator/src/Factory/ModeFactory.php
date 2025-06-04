<?php

namespace MediaWiki\Extension\PDFCreator\Factory;

use MediaWiki\Extension\PDFCreator\IExportMode;
use MediaWiki\Registration\ExtensionRegistry;
use Wikimedia\ObjectFactory\ObjectFactory;

class ModeFactory {

	/** @var ObjectFactory */
	private $objectFactory;

	/** @var array|null */
	private $modes = null;

	/**
	 *
	 * @param ObjectFactory $objectFactory
	 */
	public function __construct( ObjectFactory $objectFactory ) {
		$this->objectFactory = $objectFactory;

		$this->modes = null;
	}

	/**
	 *
	 * @param string $mode
	 * @return IExportMode
	 */
	public function getModeProvider( $mode ) {
		$this->getAllProviders();
		foreach ( $this->modes as $modeProvider ) {
			if ( $modeProvider->applies( $mode ) ) {
				return $modeProvider;
			}
		}
	}

	/**
	 *
	 * @return IExportMode[]
	 */
	public function getAllProviders() {
		if ( !$this->modes ) {
			$modeOptions = ExtensionRegistry::getInstance()->getAttribute(
				'PDFCreatorExportModeConfig'
			);
			foreach ( $modeOptions as $key => $spec ) {
				$modeProvider = $this->objectFactory->createObject( $spec );

				if ( $modeProvider instanceof IExportMode === false ) {
					continue;
				}
				$this->modes[] = $modeProvider;
			}
		}

		return $this->modes;
	}

}
