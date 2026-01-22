<?php

namespace BlueSpice\QrCode\Tag;

use MediaWiki\Html\Html;
use MediaWiki\Message\Message;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;
use MWStake\MediaWiki\Component\DynamicFileDispatcher\DynamicFileDispatcherFactory;
use MWStake\MediaWiki\Component\GenericTagHandler\ITagHandler;

class QrCodeHandler implements ITagHandler {

	public function __construct(
		private readonly DynamicFileDispatcherFactory $dfdFactory
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function getRenderedContent( string $input, array $params, Parser $parser, PPFrame $frame ): string {
		$altText = Message::newFromKey( 'bs-qr-code-alt-text', $params['page']->getText() )->text();

		$url = $this->dfdFactory->getUrl( 'qrcode', [
			'pagename' => $params['page']->getPrefixedText(),
			'query' => $params['query'] ?? '',
			'size' => $params['size'],
		] );

		$img = Html::element( 'img', [ 'src' => $url, 'alt' => $altText, 'class' => 'qrCodeImg' ] );
		if ( $params['desc'] === '' ) {
			return $img;
		}

		$text = Html::element( 'span', [ 'class' => 'qrCodeText', 'style' => 'display: block;' ], $params['desc'] );
		return $text . $img;
	}
}
