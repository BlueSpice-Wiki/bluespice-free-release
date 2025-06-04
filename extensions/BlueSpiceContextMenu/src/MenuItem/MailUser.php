<?php

namespace BlueSpice\ContextMenu\MenuItem;

use MediaWiki\Context\IContextSource;
use MediaWiki\Context\RequestContext;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\User\User;
use SpecialEmailUser;

class MailUser extends BaseUserAction {

	/**
	 *
	 * @return string
	 */
	public function getIconClass() {
		return 'message';
	}

	/**
	 *
	 * @return Message
	 */
	public function getLabelMessage() {
		return wfMessage( 'bs-contextmenu-user-mail' );
	}

	/**
	 *
	 * @return string String of the URL.
	 */
	public function getUrl() {
		return SpecialPage::getTitleFor( 'Movepage' )->getFullURL() . '/' . $this->title->getFullText();
	}

	/**
	 *
	 * @return string
	 */
	public function getId() {
		return 'bs-cm-item-move';
	}

	/**
	 *
	 * @param IContextSource $context
	 * @return bool
	 */
	public function shouldList( $context ) {
		if ( $this->targetUser ) {
			if ( class_exists( '\MediaWiki\Mail\EmailUser' ) ) {
				// MediaWiki 1.40+ required; required to work on MW 1.43+
				$emailUser = MediaWikiServices::getInstance()
					->getEmailUserFactory()
					->newEmailUser( $this->getUser() );
				return $emailUser->canSend()->isGood();
			} else {
				// Will not work on MW 1.43; to remove once MW 1.40 support is not needed
				$user = $this->getUser();
				$eMailPermissioErrors = SpecialEmailUser::getPermissionsError(
					$user,
					$context->getCsrfTokenSet()->getToken()->toString()
				);
				return $eMailPermissioErrors;
			}
		}
		return false;
	}

	/**
	 *
	 * @return User
	 */
	public function getUser() {
		$context = RequestContext::getMain();
		return $context->getUser();
	}

}
