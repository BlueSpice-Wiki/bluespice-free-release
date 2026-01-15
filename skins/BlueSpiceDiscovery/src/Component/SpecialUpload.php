<?php

namespace BlueSpice\Discovery\Component;

use MediaWiki\Context\IContextSource;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\SpecialPage\SpecialPage;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\RestrictedTextLink;

class SpecialUpload extends RestrictedTextLink {

	/** @var SpecialPage|null */
	private $specialPage;

	public function __construct() {
		parent::__construct( [] );
		$this->specialPage = MediaWikiServices::getInstance()->getSpecialPageFactory()
			->getPage( 'Upload' );
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
		return 'ga-special-upload';
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
}
