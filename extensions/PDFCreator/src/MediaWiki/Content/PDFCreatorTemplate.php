<?php

namespace MediaWiki\Extension\PDFCreator\MediaWiki\Content;

use MediaWiki\Content\TextContent;
use MWException;

class PDFCreatorTemplate extends TextContent {

	/**
	 * @param string $text
	 *
	 * @throws MWException
	 */
	public function __construct( $text ) {
		parent::__construct( $text, 'pdfcreator_template' );
	}
}
