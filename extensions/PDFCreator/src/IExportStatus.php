<?php

namespace MediaWiki\Extension\PDFCreator;

interface IExportStatus {

	/**
	 * @return array
	 */
	public function getData(): array;
}
