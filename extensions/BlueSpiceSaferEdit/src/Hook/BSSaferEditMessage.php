<?php

namespace BlueSpice\SaferEdit\Hook;

use MediaWiki\Title\Title;

interface BSSaferEditMessage {

	/**
	 * @param Title $title
	 * @param string &$message
	 */
	public function onBSSaferEditMessage( Title $title, string &$message ): void;
}
