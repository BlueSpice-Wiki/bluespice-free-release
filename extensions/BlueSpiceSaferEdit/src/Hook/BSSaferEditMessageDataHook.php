<?php

namespace BlueSpice\SaferEdit\Hook;

use MediaWiki\Title\Title;

interface BSSaferEditMessageDataHook {

	/**
	 * For basic functionality return
	 * [
	 * 	'message' => 'your-msg-key-available-on-client-side',
	 *  'params' => [ 'message', 'params' ] (optional),
	 *  'hideForCurrentUser' => true|false (optional)
	 * ]
	 * @param Title $title
	 * @param array &$data
	 */
	public function onBSSaferEditMessageData( Title $title, array &$data ): void;
}
