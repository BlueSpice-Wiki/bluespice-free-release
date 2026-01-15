<?php

namespace BlueSpice\ContextMenu\MenuItem;

use MediaWiki\Message\Message;

class Purge extends Base {

	/**
	 *
	 * @return string
	 */
	public function getIconClass() {
		return 'reload';
	}

	/**
	 *
	 * @return Message
	 */
	public function getLabelMessage() {
		return wfMessage( 'bs-contextmenu-page-purge' );
	}

	/**
	 *
	 * @return string String of the URL.
	 */
	public function getUrl() {
		return $this->title->getLocalUrl( [ 'action' => 'purge' ] );
	}

	/**
	 *
	 * @return string
	 */
	public function getId() {
		return 'bs-cm-item-purge';
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
				->userCan( 'purge', $context->getUser(), $this->title );
	}

}
