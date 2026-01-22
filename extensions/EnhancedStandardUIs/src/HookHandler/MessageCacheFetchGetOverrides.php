<?php

namespace MediaWiki\Extension\EnhancedStandardUIs\HookHandler;

use MediaWiki\Cache\Hook\MessageCacheFetchOverridesHook;

class MessageCacheFetchGetOverrides implements MessageCacheFetchOverridesHook {

	/**
	 *
	 * @inheritDoc
	 */
	public function onMessageCacheFetchOverrides( array &$keys ): void {
		$keys['username'] = 'enhanced-pref-username';

		$keys[ 'prefs-memberingroups' ] = 'enhanced-prefs-memberingroups';
	}
}
