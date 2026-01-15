<?php

namespace MediaWiki\Extension\PDFCreator;

use MediaWiki\Extension\PDFCreator\Utility\ExportContext;

interface IStylesheetsProvider {

	/**
	 * [ 'name' => 'path' ]
	 *
	 * @param string $module
	 * @param ExportContext $context
	 * @return array
	 */
	public function execute( string $module, ExportContext $context ): array;
}
