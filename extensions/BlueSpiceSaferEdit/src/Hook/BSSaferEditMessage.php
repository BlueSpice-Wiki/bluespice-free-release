<?php

namespace BlueSpice\SaferEdit\Hook;

use MediaWiki\Title\Title;

/**
 * @deprecated since 5.2 - use BSSaferEditMessageData
 */
interface BSSaferEditMessage {

	/**
	 * @param Title $title
	 * @param string &$message
	 */
	public function onBSSaferEditMessage( Title $title, string &$message ): void;
}
