<?php

namespace MWStake\MediaWiki\Component\GenericTagHandler\Hook;

interface MWStakeGenericTagHandlerInitTagsHook {

	/**
	 * @param array &$tags
	 * @return mixed
	 */
	public function onMWStakeGenericTagHandlerInitTags( array &$tags );
}
