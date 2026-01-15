<?php

namespace BlueSpice\Discovery\HookHandler\MessageCacheFetchOverrides;

use MediaWiki\Cache\Hook\MessageCacheFetchOverridesHook;

class FixMessages implements MessageCacheFetchOverridesHook {

	/**
	 *
	 * @inheritDoc
	 */
	public function onMessageCacheFetchOverrides( array &$keys ): void {
		$keys['table-of-contents-hide-button-aria-label'] = 'bs-toc-hide-button-aria-label';
	}
}
