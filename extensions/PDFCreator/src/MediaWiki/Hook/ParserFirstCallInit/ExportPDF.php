<?php

namespace MediaWiki\Extension\PDFCreator\MediaWiki\Hook\ParserFirstCallInit;

use MediaWiki\Hook\ParserFirstCallInitHook;
use MediaWiki\Html\Html;
use MediaWiki\Json\FormatJson;
use MediaWiki\Message\Message;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;
use MediaWiki\Title\TitleFactory;

class ExportPDF implements ParserFirstCallInitHook {

	public const NAME = 'exportpdf';

	/**
	 * @var array
	 */
	private static $counter = [];

	/** @var TitleFactory */
	private $titleFactory;

	/**
	 * @param TitleFactory $titleFactory
	 */
	public function __construct( TitleFactory $titleFactory ) {
		$this->titleFactory = $titleFactory;
	}

	/**
	 * @param Parser $parser
	 */
	public function onParserFirstCallInit( $parser ) {
		$parser->setHook( static::NAME, [ $this, 'onExportPDF' ] );
	}

	/**
	 * @param string|null $input
	 * @param array $args
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return string
	 */
	public function onExportPDF( ?string $input, array $args, Parser $parser,
		PPFrame $frame ) {
		if ( isset( static::$counter[spl_object_id( $parser )] ) ) {
			static::$counter[spl_object_id( $parser )]++;
		} else {
			static::$counter[spl_object_id( $parser )] = 0;
		}

		$specification = [];
		if ( isset( $args['template'] ) ) {
			$specification['template'] = $parser->recursiveTagParse( $args['template'], $frame );
		}
		if ( isset( $args['mode'] ) ) {
			$specification['mode'] = $parser->recursiveTagParse( $args['mode'], $frame );
		}
		if ( isset( $args['page'] ) ) {
			$page = $parser->recursiveTagParse( $args['page'], $frame );
			$title = $this->titleFactory->newFromText( $page );
			if ( $title ) {
				$specification['pageid'] = $title->getArticleID();
			}
		}

		$label = Message::newFromKey( 'pdfcreator-export-pdf-tag-label' );
		if ( isset( $args['label' ] ) ) {
			$label = $parser->recursiveTagParse( $args['label'], $frame );
		}
		$count = static::$counter[spl_object_id( $parser )];

		$parser->getOutput()->addModules( [ 'ext.pdfcreator.tag.export' ] );
		$out = Html::element( 'a', [
			'class' => 'pdfcreator-export',
			'data-export' => FormatJson::encode( $specification ),
			'href' => '',
			'data-no' => $count,
		], $label );

		return $out;
	}

}
