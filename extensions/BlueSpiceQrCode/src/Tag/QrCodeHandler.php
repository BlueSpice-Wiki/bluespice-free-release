<?php

namespace BlueSpice\QrCode\Tag;

use BlueSpice\QrCode\Tag\QrCode as TagQrCode;
use BlueSpice\Tag\Handler;
use MediaWiki\Context\RequestContext;
use MediaWiki\Html\Html;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;
use MediaWiki\Title\TitleFactory;
use MWStake\MediaWiki\Component\DynamicFileDispatcher\DynamicFileDispatcherFactory;

class QrCodeHandler extends Handler {

	/** @var RequestContext */
	private $context;

	/** @var TitleFactory */
	private $titleFactory;

	/** @var DynamicFileDispatcherFactory */
	private $dfdFactory;

	/**
	 * @param string $processedInput
	 * @param array $processedArgs
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @param RequestContext $context
	 * @param TitleFactory $titleFactory
	 * @param DynamicFileDispatcherFactory $dfdFactory
	 */
	public function __construct( $processedInput, array $processedArgs, Parser $parser,
		PPFrame $frame, RequestContext $context, TitleFactory $titleFactory,
		DynamicFileDispatcherFactory $dfdFactory
	) {
		parent::__construct( $processedInput, $processedArgs, $parser, $frame );
		$this->context = $context;
		$this->titleFactory = $titleFactory;
		$this->dfdFactory = $dfdFactory;
	}

	/**
	 * @inheritDoc
	 */
	public function handle() {
		$page = $this->processedArgs[TagQrCode::PARAM_PAGE];
		$query = $this->processedArgs[TagQrCode::PARAM_QUERY];
		$desc = $this->processedArgs[TagQrCode::PARAM_DESC];
		$size = $this->processedArgs[TagQrCode::PARAM_SIZE];
		$title = null;

		if ( $page !== '' ) {
			$title = $this->titleFactory->newFromText( $page );
		}
		if ( !$title ) {
			$title = $this->context->getTitle();
		}
		if ( !$title ) {
			return '';
		}

		$altText = $this->context->msg( 'bs-qr-code-alt-text', $title->getText() );

		$url = $this->dfdFactory->getUrl( 'qrcode', [
			'pagename' => $title->getPrefixedText(),
			'query' => $query,
			'size' => $size,
		] );

		$img = Html::element( 'img', [ 'src' => $url, 'alt' => $altText, 'class' => 'qrCodeImg' ] );
		if ( $desc === '' ) {
			return $img;
		}

		$text = Html::element( 'span', [ 'class' => 'qrCodeText', 'style' => 'display: block;' ], $desc );
		return $text . $img;
	}

}
