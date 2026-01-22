<?php

namespace BlueSpice\WhoIsOnline\Hook;

use BlueSpice\WhoIsOnline\Tag\Count;
use BlueSpice\WhoIsOnline\Tag\Popup;
use MWStake\MediaWiki\Component\GenericTagHandler\Hook\MWStakeGenericTagHandlerInitTagsHook;

class RegisterTags implements MWStakeGenericTagHandlerInitTagsHook {

	/**
	 * @inheritDoc
	 */
	public function onMWStakeGenericTagHandlerInitTags( array &$tags ) {
		$tags[] = new Count();
		$tags[] = new Popup();
	}
}
