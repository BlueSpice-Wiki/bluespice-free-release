<?php

namespace MediaWiki\Extension\EnhancedStandardUIs;

use Exception;
use MediaWiki\Message\Message;
use MediaWiki\SpecialPage\SpecialPage;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\RestrictedTextLink;

class FilelistGlobalAction extends RestrictedTextLink {

	public function __construct() {
		parent::__construct( [] );
	}

	/**
	 *
	 * @return string
	 */
	public function getId(): string {
		return 'ga-enhanced-standard-uis-filelist';
	}

	/**
	 *
	 * @return array
	 */
	public function getPermissions(): array {
		return [ 'read' ];
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	public function getHref(): string {
		$tool = SpecialPage::getTitleFor( 'EnhancedFilelist' );
		return $tool->getLocalURL();
	}

	/**
	 *
	 * @return Message
	 */
	public function getText(): Message {
		return Message::newFromKey( 'enhanced-standard-uis-filelist-ga-title' );
	}

	/**
	 *
	 * @return Message
	 */
	public function getTitle(): Message {
		return Message::newFromKey( 'enhanced-standard-uis-filelist-ga-title' );
	}

	/**
	 *
	 * @return Message
	 */
	public function getAriaLabel(): Message {
		return Message::newFromKey( 'enhanced-standard-uis-filelist-ga-aria' );
	}
}
