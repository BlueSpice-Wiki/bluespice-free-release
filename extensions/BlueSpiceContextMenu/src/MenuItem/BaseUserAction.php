<?php

namespace BlueSpice\ContextMenu\MenuItem;

use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use MediaWiki\User\User;

abstract class BaseUserAction extends Base {

	/**
	 *
	 * @var Title
	 */
	protected $title = null;

	/**
	 *
	 * @var User
	 */
	protected $targetUser = null;

	/**
	 *
	 * @param Title $title
	 */
	public function __construct( $title ) {
		parent::__construct( $title );
		if ( $title->getNamespace() === NS_USER ) {
			$this->targetUser = MediaWikiServices::getInstance()->getUserFactory()
				->newFromName( $title->getPrefixedDBkey() );
		}
	}

}
