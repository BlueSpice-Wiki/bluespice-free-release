<?php

namespace MediaWiki\Extension\PDFCreator;

use MediaWiki\Extension\PDFCreator\Utility\ExportResources;

interface IExportBackend {

	/**
	 * Return path to temporary file
	 *
	 * @param ExportResources $resources
	 * @param array $params
	 * @return string
	 */
	public function create( ExportResources $resources, array $params = [] ): string;
}
