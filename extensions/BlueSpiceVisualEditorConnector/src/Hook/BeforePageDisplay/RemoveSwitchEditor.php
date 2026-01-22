<?php

namespace BlueSpice\VisualEditorConnector\Hook\BeforePageDisplay;

use MediaWiki\Output\Hook\BeforePageDisplayHook;
use MediaWiki\Registration\ExtensionRegistry;

class RemoveSwitchEditor implements BeforePageDisplayHook {

	/**
	 * @inheritDoc
	 */
	public function onBeforePageDisplay( $out, $skin ): void {
		if ( ExtensionRegistry::getInstance()->isLoaded( 'VEForAll' ) ) {
			$out->addModules( [ 'ext.bluespice.visualEditorConnector.vEForAll.removeSwitchEditor' ] );
		}
	}

}
