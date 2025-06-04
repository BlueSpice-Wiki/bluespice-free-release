<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

use MediaWiki\Html\TemplateParser;

class MustacheTemplateParser {

	/**
	 * @param string $dir
	 * @param string $name
	 * @param array $params
	 * @return string
	 */
	public function execute( string $dir, string $name, array $params = [] ): string {
		$parser = new TemplateParser( $dir );
		return $parser->processTemplate( $name, $params );
	}
}
