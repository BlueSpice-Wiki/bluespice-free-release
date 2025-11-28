<?php

namespace MediaWiki\Extension\PDFCreator\Processor;

use MediaWiki\Extension\PDFCreator\IProcessor;
use MediaWiki\Extension\PDFCreator\Utility\ExportContext;
use MediaWiki\Extension\PDFCreator\Utility\ExportPage;
use MediaWiki\Extension\PDFCreator\Utility\WikiLinker;
use MediaWiki\Utils\UrlUtils;

/**
 * This class has to run after PageLinkerProcessor
 */
class WikiLinkerProcessor implements IProcessor {

	/**
	 * @param UrlUtils $urlUtils
	 */
	public function __construct( private readonly UrlUtils $urlUtils ) {
	}

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
		$wikiLinker = new WikiLinker( $this->urlUtils );
		$pages = $wikiLinker->execute( $pages );
	}

	/**
	 * @inheritDoc
	 */
	public function getPosition(): int {
		return 80;
	}
}
