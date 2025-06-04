<?php

namespace BlueSpice\VisualEditorConnector\Integration\PDFCreator\StylesheetsProvider;

use MediaWiki\Extension\PDFCreator\IStylesheetsProvider;
use MediaWiki\Extension\PDFCreator\Utility\ExportContext;

class Colors implements IStylesheetsProvider {

	/**
	 * @param string $module
	 * @param ExportContext $context
	 * @return array
	 */
	public function execute( string $module, ExportContext $context ): array {
		$dir = dirname( __DIR__, 4 );

		$name = 'ext.bluespice.visualEditorConnector.colors.export.css';
		return [
			$name => "$dir/resources/$name"
		];
	}
}
