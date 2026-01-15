<?php

namespace BlueSpice\Discovery\Component;

use MediaWiki\Context\IContextSource;
use MediaWiki\Language\RawMessage;
use MediaWiki\Message\Message;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\SpecialPage\SpecialPageFactory;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\RestrictedTextLink;

class SpecialSpecialPages extends RestrictedTextLink {

	/** @var SpecialPage|null */
	private $specialPage;

	public function __construct( SpecialPageFactory $specialPageFactory ) {
		parent::__construct( [] );
		$this->specialPage = $specialPageFactory->getPage( 'Specialpages' );
	}

	/**
	 * @inheritDoc
	 */
	public function shouldRender( IContextSource $context ): bool {
		return (bool)$this->specialPage;
	}

	/**
	 *
	 * @return string
	 */
	public function getId(): string {
		return 'ga-special-specialpages';
	}

	/**
	 *
	 * @return string
	 */
	public function getHref(): string {
		return $this->specialPage->getPageTitle()->getLocalURL();
	}

	/**
	 *
	 * @return array
	 */
	public function getPermissions(): array {
		return [ 'edit' ];
	}

	/**
	 *
	 * @return Message
	 */
	public function getText(): Message {
		return $this->ensureMessageObject( $this->specialPage->getDescription() );
	}

	/**
	 *
	 * @return Message
	 */
	public function getTitle(): Message {
		return $this->ensureMessageObject( $this->specialPage->getDescription() );
	}

	/**
	 *
	 * @return Message
	 */
	public function getAriaLabel(): Message {
		return $this->ensureMessageObject( $this->specialPage->getDescription() );
	}

	/**
	 * @param string|Message $stringOrMessage
	 * @return Message
	 */
	protected function ensureMessageObject( $stringOrMessage ) {
		return $stringOrMessage instanceof Message ? $stringOrMessage : new RawMessage( $stringOrMessage );
	}
}
