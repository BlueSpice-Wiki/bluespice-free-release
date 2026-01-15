<?php

namespace BlueSpice\Discovery\BackLinkProvider;

use BlueSpice\Discovery\IBackLinkProvider;
use MediaWiki\Message\Message;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;

class PagesBackLinkProvider implements IBackLinkProvider {

	/** @var TitleFactory */
	private $titleFactory;

	/** @var Title */
	private $backToTitle = null;

	public function __construct( TitleFactory $titleFactory ) {
		$this->titleFactory = $titleFactory;
		$this->backToTitle = null;
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function applies( $context ): bool {
		$request = $context->getRequest();
		$backToValue = $request->getVal( 'backTo' );

		if ( !$backToValue ) {
			return false;
		}
		$this->backToTitle = $this->titleFactory->newFromText( urldecode( $backToValue ) );
		return $this->backToTitle instanceof Title;
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function getHref(): string {
		return $this->backToTitle->getLocalURL();
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function getLabel(): Message {
		$titleText = $this->backToTitle->getText();
		if ( $this->backToTitle->isSpecialPage() ) {
			// some pages could have fragments which are set as part of title
			// so we only need the first part of title
			// ERM41535
			$titleParts = explode( '/', $titleText );
			$titleText = $this->backToTitle->getNSText() . ':' . $titleParts[0];
		}
		return Message::newFromKey( 'bs-discovery-back-to-page', $titleText );
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function getTitle(): Message {
		return Message::newFromKey( 'bs-discovery-back-to-page-title', $this->backToTitle->getText() );
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function getAriaLabel(): Message {
		return Message::newFromKey( 'bs-discovery-back-to-page', $this->backToTitle->getText() );
	}
}
