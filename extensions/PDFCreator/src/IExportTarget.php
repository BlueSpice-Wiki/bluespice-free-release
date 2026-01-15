<?php

namespace MediaWiki\Extension\PDFCreator;

interface IExportTarget {

	/**
	 * @param string $pdfData
	 * @param array $params
	 * @return ITargetResult
	 */
	public function execute( string $pdfData, $params = [] ): ITargetResult;
}
