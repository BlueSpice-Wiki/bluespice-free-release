<?php

namespace MediaWiki\Extension\FlexiSkin;

interface FlexiSkinAfterLoadHook {
	/**
	 * @param IFlexiSkin $skin
	 * @return void
	 */
	public function onFlexiSkinAfterLoad( IFlexiSkin $skin );
}
