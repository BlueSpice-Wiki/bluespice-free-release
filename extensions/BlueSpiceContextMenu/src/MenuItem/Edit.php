<?php

namespace BlueSpice\ContextMenu\MenuItem;

use MediaWiki\Context\IContextSource;
use MediaWiki\Message\Message;

class Edit extends Base {

	/**
	 *
	 * @return string
	 */
	public function getIconClass() {
		return 'edit';
	}

	public function getFlags(): array {
		return [ 'progressive' ];
	}

	/**
	 *
	 * @return Message
	 */
	public function getLabelMessage() {
		return wfMessage( 'bs-contextmenu-page-edit' );
	}

	/**
	 *
	 * @return string String of the URL.
	 */
	public function getUrl() {
		return $this->title->getLocalUrl( [ 'action' => 'edit' ] );
	}

	/**
	 *
	 * @return string
	 */
	public function getId() {
		return 'bs-cm-item-edit';
	}

	/**
	 *
	 * @return int
	 */
	public function getPosition() {
		return 50;
	}

	/**
	 * @return bool
	 */
	public function isPrimary(): bool {
		return true;
	}

	/**
	 *
	 * @param IContextSource $context
	 * @return bool
	 */
	public function shouldList( $context ) {
		return \MediaWiki\MediaWikiServices::getInstance()
			->getPermissionManager()
			->userCan( 'edit', $context->getUser(), $this->title );
	}

}
