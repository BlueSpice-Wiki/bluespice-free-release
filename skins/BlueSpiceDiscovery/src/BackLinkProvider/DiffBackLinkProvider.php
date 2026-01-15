<?php

namespace BlueSpice\Discovery\BackLinkProvider;

use BlueSpice\Discovery\IBackLinkProvider;
use MediaWiki\Message\Message;
use MediaWiki\Title\Title;

class DiffBackLinkProvider implements IBackLinkProvider {

	/** @var Title */
	private $backToTitle = null;

	/**
	 *
	 */
	public function __construct() {
		$this->backToTitle = null;
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function applies( $context ): bool {
		$request = $context->getRequest();
		$diffVal = $request->getVal( 'diff' );
		if ( !$diffVal ) {
			return false;
		}
		$diffTitle = $context->getTitle();
		if ( !$diffTitle ) {
			return false;
		}
		$this->backToTitle = $diffTitle;
		return true;
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function getHref(): string {
		return $this->backToTitle->getLocalURL( 'action=history' );
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function getLabel(): Message {
		return Message::newFromKey( 'bs-discovery-back-to-version-history' );
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function getTitle(): Message {
		return Message::newFromKey( 'bs-discovery-back-to-version-history-title', $this->backToTitle->getFullText() );
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function getAriaLabel(): Message {
		return Message::newFromKey( 'bs-discovery-back-to-version-history' );
	}
}
