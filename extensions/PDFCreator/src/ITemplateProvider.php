<?php

namespace MediaWiki\Extension\PDFCreator;

use MediaWiki\Extension\PDFCreator\Utility\ExportContext;
use MediaWiki\Extension\PDFCreator\Utility\Template;

interface ITemplateProvider {

	/**
	 * @return array
	 */
	public function getTemplateNames(): array;

	/**
	 * @param ExportContext $context
	 * @param string $name
	 * @return Template|null
	 */
	public function getTemplate( ExportContext $context, string $name = '' ): ?Template;
}
