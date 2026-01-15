<?php

namespace MediaWiki\Extension\PDFCreator;

use MediaWiki\Extension\PDFCreator\Utility\ExportContext;
use MediaWiki\Extension\PDFCreator\Utility\ExportResult;
use MediaWiki\Extension\PDFCreator\Utility\ExportSpecification;

interface IExportModule {

	/**
	 * @return string
	 */
	public function getName(): string;

	/**
	 * @param ExportSpecification $specification
	 * @param ExportContext $context
	 * @return ExportResult
	 */
	public function execute( ExportSpecification $specification, ExportContext $context ): ExportResult;
}
