<?php

namespace BlueSpice\Discovery\Component;

use MediaWiki\Message\Message;
use MediaWiki\Title\Title;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\RestrictedTextLink;

class SpecialMainPage extends RestrictedTextLink {
	/**
	 *
	 */
	public function __construct() {
		parent::__construct( [] );
	}

	/**
	 *
	 * @return string
	 */
	public function getId(): string {
		return 'n-mainpage-description';
	}

	/**
	 *
	 * @return string
	 */
	public function getHref(): string {
		/** @var Title */
		$mainpage = Title::newMainPage();
		return $mainpage->getLocalURL();
	}

	/**
	 *
	 * @return array
	 */
	public function getPermissions(): array {
		return [ 'read' ];
	}

	/**
	 *
	 * @return Message
	 */
	public function getText(): Message {
		return new Message( 'mainpage' );
	}

	/**
	 *
	 * @return Message
	 */
	public function getTitle(): Message {
		return new Message( 'mainpage-description' );
	}

	/**
	 *
	 * @return Message
	 */
	public function getAriaLabel(): Message {
		return new Message( 'mainpage' );
	}
}
