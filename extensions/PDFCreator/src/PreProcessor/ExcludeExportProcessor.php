<?php

namespace MediaWiki\Extension\PDFCreator\PreProcessor;

use MediaWiki\Extension\PDFCreator\IPreProcessor;
use MediaWiki\Extension\PDFCreator\Utility\ExcludeExportUpdater;
use MediaWiki\Extension\PDFCreator\Utility\ExportContext;

class ExcludeExportProcessor implements IPreProcessor {

	/**
	 * @param ExportPage[] &$pages
	 * @param array &$images
	 * @param array &$attachments
	 * @param ExportContext|null $context
	 * @param string $module
	 * @param array $params
	 * @return void
	 */
	public function execute(
		array &$pages, array &$images, array &$attachments,
		?ExportContext $context = null, string $module = '', $params = []
	): void {
		$excludeUpdater = new ExcludeExportUpdater();
		$excludeUpdater->execute( $pages );
	}

}
