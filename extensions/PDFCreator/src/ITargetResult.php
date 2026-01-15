<?php

namespace MediaWiki\Extension\PDFCreator;

use MediaWiki\Extension\PDFCreator\Utility\ExportStatus;

interface ITargetResult {

	/**
	 * @return ExportStatus
	 */
	public function getStatus(): ExportStatus;

	/**
	 * @return string
	 */
	public function getTarget(): string;

	/**
	 * @return string
	 */
	public function getFilename(): string;

	/**
	 * @return array
	 */
	public function getData(): array;
}
