<?php

namespace BlueSpice\Discovery\Integration\PDFCreator\StylesheetsProvider;

use MediaWiki\Extension\PDFCreator\IStylesheetsProvider;
use MediaWiki\Extension\PDFCreator\Utility\ExportContext;

class BootstrapFontStyles implements IStylesheetsProvider {

	/**
	 * @inheritDoc
	 */
	public function execute( string $module, ExportContext $context ): array {
		$dir = dirname( __DIR__, 4 );
		return [
			'bootstrap-icons-fonts.css' => "$dir/data/PDFTemplates/common/stylesheets/bootstrap-icons-fonts.css",
			'bootstrap-icons.ttf' => "$dir/data/PDFTemplates/common/fonts/bootstrap-icons.ttf",
			'bootstrap-icons.css' => "$dir/resources/bootstrap/icons/bootstrap-icons.css"
		];
	}
}
