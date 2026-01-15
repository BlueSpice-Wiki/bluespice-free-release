<?php

namespace MediaWiki\Extension\PDFCreator;

use MediaWiki\Extension\PDFCreator\Factory\ExportModuleFactory;
use MediaWiki\Extension\PDFCreator\Utility\ExportContext;
use MediaWiki\Extension\PDFCreator\Utility\ExportResult;
use MediaWiki\Extension\PDFCreator\Utility\ExportSpecification;
use MediaWiki\Extension\PDFCreator\Utility\ExportStatus;
use MediaWiki\Logger\LoggerFactory;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class PDFCreator implements LoggerAwareInterface {

	public const INTRO = 'intro';
	public const HEADER = 'header';
	public const CONTENT = 'content';
	public const FOOTER = 'footer';
	public const OUTRO = 'outro';
	public const HTML_STUB = '<html><head></head><body></body></html>';

	/** @var LoggerInterface */
	private $logger = null;

	/** @var ExportModuleFactory */
	private $exportModuleFactory;

	/**
	 * @param ExportModuleFactory $exportModuleFactory
	 */
	public function __construct( ExportModuleFactory $exportModuleFactory ) {
		$this->exportModuleFactory = $exportModuleFactory;
	}

	/**
	 * @param ExportSpecification $specification
	 * @param ExportContext $context
	 * @return ExportResult
	 */
	public function create( ExportSpecification $specification, ExportContext $context ): ExportResult {
		if ( $this->logger instanceof LoggerInterface === false ) {
			$this->logger = LoggerFactory::getInstance( 'PDFCreator' );
		}

		$module = $this->getModule( $specification->getModule() );
		if ( $module instanceof IExportModule === false ) {
			$errorTxt = $specification->getModule() . ' is not a valid module.';
			$this->logger->error( $errorTxt );
			$status = new ExportStatus( false, $errorTxt );
			return new ExportResult( $status, null );
		}

		if ( $module instanceof LoggerAwareInterface ) {
			$module->setLogger( $this->logger );
		}
		// TODO: $module can be null
		$status = $module->execute( $specification, $context );

		return $status;
	}

	/**
	 * @param LoggerInterface $logger
	 * @return void
	 */
	public function setLogger( LoggerInterface $logger ): void {
		$this->logger = $logger;
	}

	/**
	 * @param string $name
	 * @return IExportModule|null
	 */
	private function getModule( string $name ): ?IExportModule {
		$module = $this->exportModuleFactory->getModule( $name );
		return $module;
	}

}
