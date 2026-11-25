<?php

namespace MediaWiki\Extension\PDFCreator\MediaWiki\Hook\ParserFirstCallInit;

use MediaWiki\Hook\ParserFirstCallInitHook;
use MediaWiki\Html\Html;
use MediaWiki\Message\Message;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;

class NoExportEnd implements ParserFirstCallInitHook {

	public const NAME = 'pdfexcludeend';

	/**
	 * @var array
	 */
	private static $counter = [];

	public function __construct() {
	}

	/**
	 * @param Parser $parser
	 */
	public function onParserFirstCallInit( $parser ) {
		$parser->setHook( static::NAME, [ $this, 'onExcludeExport' ] );
	}

	/**
	 * @param string|null $input
	 * @param array $args
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return string
	 */
	public function onExcludeExport( ?string $input, array $args, Parser $parser,
		PPFrame $frame ) {
		if ( isset( static::$counter[spl_object_id( $parser )] ) ) {
			static::$counter[spl_object_id( $parser )]++;
		} else {
			static::$counter[spl_object_id( $parser )] = 0;
		}
		$parser->getOutput()->addModuleStyles( [ 'ext.pdfcreator.tag.viewmode.styles' ] );

		$out = Html::element( 'div', [
			'class' => 'pdfcreator-excludeend'
		], Message::newFromKey( 'pdfcreator-tag-no-export-end-ve-label' )->text() );
		return $out;
	}

}
