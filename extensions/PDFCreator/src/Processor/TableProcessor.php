<?php

namespace MediaWiki\Extension\PDFCreator\Processor;

use MediaWiki\Extension\PDFCreator\IProcessor;
use MediaWiki\Extension\PDFCreator\Utility\ExportContext;
use MediaWiki\Extension\PDFCreator\Utility\TableHeadsDuplicator;
use MediaWiki\Extension\PDFCreator\Utility\TableWidthUpdater;

class TableProcessor implements IProcessor {

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
		$tableWidthUpdater = new TableWidthUpdater();
		$tableWidthUpdater->execute( $pages );

		$tableHeadsDuplicator = new TableHeadsDuplicator();
		$tableHeadsDuplicator->execute( $pages );
	}

	/**
	 * @inheritDoc
	 */
	public function getPosition(): int {
		return 80;
	}
}
