<?php

namespace BlueSpice\ContextMenu\MenuItem;

use MediaWiki\Message\Message;

class EditUserTalk extends BaseUserAction {

	/**
	 *
	 * @return string
	 */
	public function getIconClass() {
		return 'edit';
	}

	/**
	 *
	 * @return Message
	 */
	public function getLabelMessage() {
		return wfMessage( 'bs-contextmenu-user-talk' );
	}

	/**
	 *
	 * @return string String of the URL.
	 */
	public function getUrl() {
		return $this->targetUser->getTalkPage()->getLocalUrl( [ 'action' => 'edit' ] );
	}

	/**
	 *
	 * @return string
	 */
	public function getId() {
		return 'bs-cm-item-usertalk';
	}

	/**
	 *
	 * @param \Context $context
	 * @return bool
	 */
	public function shouldList( $context ) {
		if ( $this->targetUser ) {
			return \MediaWiki\MediaWikiServices::getInstance()
				->getPermissionManager()
				->userCan(
					'edit',
					$context->getUser(),
					$this->targetUser->getTalkPage()
				);
		}
		return false;
	}

}
