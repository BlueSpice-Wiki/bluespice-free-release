<?php

namespace MediaWiki\Extension\PDFCreator;

use MediaWiki\Extension\PDFCreator\Utility\ExportContext;

interface IPostProcessor {

	/**
	 * @param string &$html
	 * @param ExportContext|null $context
	 * @param string $module
	 * @param array $params
	 * @return void
	 */
	public function execute( string &$html, ?ExportContext $context = null, string $module = '', $params = [] ): void;
}
