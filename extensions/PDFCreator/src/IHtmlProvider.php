<?php

namespace MediaWiki\Extension\PDFCreator;

use DOMDocument;
use MediaWiki\Extension\PDFCreator\Utility\ExportContext;
use MediaWiki\Extension\PDFCreator\Utility\PageSpec;
use MediaWiki\Extension\PDFCreator\Utility\Template;

interface IHtmlProvider {

	/**
	 * @return string
	 */
	public function getKey(): string;

	/**
	 * @param PageSpec $pageSpec
	 * @param Template $template
	 * @param ExportContext $context
	 * @param string $workspace
	 * @return DOMDocument
	 */
	public function getDOMDocument(
		PageSpec $pageSpec, Template $template, ExportContext $context, string $workspace
	): DOMDocument;
}
