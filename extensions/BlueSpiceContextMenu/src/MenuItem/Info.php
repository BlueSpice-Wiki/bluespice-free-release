<?php

namespace BlueSpice\ContextMenu\MenuItem;

use MediaWiki\Message\Message;

class Info extends Base {

	/**
	 *
	 * @return string
	 */
	public function getIconClass() {
		return 'info';
	}

	/**
	 *
	 * @return Message
	 */
	public function getLabelMessage() {
		return wfMessage( 'bs-contextmenu-page-info' );
	}

	/**
	 *
	 * @return string String of the URL.
	 */
	public function getUrl() {
		return $this->title->getLocalUrl( [ 'action' => 'info' ] );
	}

	/**
	 *
	 * @return string
	 */
	public function getId() {
		return 'bs-cm-item-info';
	}

	/**
	 *
	 * @param \Context $context
	 * @return bool
	 */
	public function shouldList( $context ) {
		return $this->title->isKnown() &&
			\MediaWiki\MediaWikiServices::getInstance()
				->getPermissionManager()
				->userCan( 'read', $context->getUser(), $this->title );
	}

}
