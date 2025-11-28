<?php

namespace MediaWiki\Extension\PDFCreator\MediaWiki\Hook\ParserFirstCallInit;

use MediaWiki\Hook\ParserFirstCallInitHook;
use MediaWiki\Html\Html;
use MediaWiki\Message\Message;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;

class PageBreak implements ParserFirstCallInitHook {

	public const NAME = 'pdfpagebreak';

	public function __construct() {
	}

	/**
	 * @param Parser $parser
	 */
	public function onParserFirstCallInit( $parser ) {
		$parser->setHook( static::NAME, [ $this, 'onPDFPageBreak' ] );
	}

	/**
	 * @param string|null $input
	 * @param array $args
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return string
	 */
	public function onPDFPageBreak( ?string $input, array $args, Parser $parser,
		PPFrame $frame ) {
		$parser->getOutput()->addModuleStyles( [ 'ext.pdfcreator.tag.viewmode.styles' ] );

		$out = Html::element(
			'div',
			[ 'class' => 'pagebreak' ],
			Message::newFromKey( 'pdfcreator-tag-pagebreak-label' )->text()
		);
		return $out;
	}

}
