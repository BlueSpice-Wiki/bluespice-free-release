<?php

namespace BlueSpice\ContextMenu\MenuItem;

use MediaWiki\Message\Message;

class Download extends BaseFileAction {

	/**
	 *
	 * @return string
	 */
	public function getIconClass() {
		return 'download';
	}

	/**
	 *
	 * @return Message
	 */
	public function getLabelMessage() {
		return wfMessage( 'bs-contextmenu-file-download' );
	}

	/**
	 *
	 * @return string String of the URL.
	 */
	public function getUrl() {
		// add a query parameter to force download
		return $this->file->getURL() . "?download=1";
	}

	/**
	 *
	 * @return string
	 */
	public function getId() {
		return 'bs-cm-item-download';
	}

	/**
	 *
	 * @param \Context $context
	 * @return bool
	 */
	public function shouldList( $context ) {
		$ns = $this->title->getNamespace();
		if ( $this->file ) {
			if ( $this->file->exists() ) {
				return \MediaWiki\MediaWikiServices::getInstance()
					->getPermissionManager()
					->userCan( 'read', $context->getUser(), $this->title );
			}
		}
		return false;
	}

}
