<?php

namespace MediaWiki\Extension\PDFCreator;

use MediaWiki\Extension\PDFCreator\Utility\ExportSpecification;

interface ISpecificationAware {

	/**
	 * @param ExportSpecification $specification
	 *
	 * @return void
	 */
	public function setExportSpecification( ExportSpecification $specification ): void;
}
