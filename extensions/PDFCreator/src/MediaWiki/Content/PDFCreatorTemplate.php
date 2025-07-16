<?php

namespace MediaWiki\Extension\PDFCreator\MediaWiki\Content;

use InvalidArgumentException;
use MediaWiki\Content\TextContent;

class PDFCreatorTemplate extends TextContent {

	/**
	 * @param string $text
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( $text ) {
		parent::__construct( $text, 'pdfcreator_template' );
	}
}
