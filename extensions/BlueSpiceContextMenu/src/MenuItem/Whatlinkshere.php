<?php

namespace BlueSpice\ContextMenu\MenuItem;

use MediaWiki\Message\Message;
use MediaWiki\SpecialPage\SpecialPage;

class Whatlinkshere extends Base {

	/**
	 *
	 * @return string
	 */
	public function getIconClass() {
		return 'search';
	}

	/**
	 *
	 * @return Message
	 */
	public function getLabelMessage() {
		return wfMessage( 'whatlinkshere' );
	}

	/**
	 *
	 * @return string String of the URL.
	 */
	public function getUrl() {
		return SpecialPage::getTitleFor( 'Whatlinkshere' )->
			getLinkURL( [ 'target' => $this->title->getFullText() ] );
	}

	/**
	 *
	 * @return string
	 */
	public function getId() {
		return 'bs-cm-item-whatlinkshere';
	}

	/**
	 *
	 * @param \Context $context
	 * @return bool
	 */
	public function shouldList( $context ) {
		return \MediaWiki\MediaWikiServices::getInstance()
			->getPermissionManager()
			->userCan( 'read', $context->getUser(), $this->title );
	}

}
