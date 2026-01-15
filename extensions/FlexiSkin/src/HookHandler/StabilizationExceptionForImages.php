<?php

namespace MediaWiki\Extension\FlexiSkin\HookHandler;

use MediaWiki\Extension\ContentStabilization\Hook\Interfaces\ContentStabilizationIsStabilizationEnabledHook;
use MediaWiki\Page\PageIdentity;

class StabilizationExceptionForImages implements ContentStabilizationIsStabilizationEnabledHook {

	/**
	 * Make sure that the logo and favicon images are not stabilized
	 *
	 * @param PageIdentity $page
	 * @param bool &$result
	 *
	 * @return void
	 */
	public function onContentStabilizationIsStabilizationEnabled( PageIdentity $page, bool &$result ): void {
		if ( $page->getNamespace() !== NS_FILE ) {
			return;
		}
		$dbkey = strtolower( $page->getDBkey() );
		foreach ( [ 'logo', 'favicon' ] as $imageKey ) {
			if ( strpos( $dbkey, 'flexiskin-images-' . $imageKey ) === 0 ) {
				$result = false;
				return;
			}
		}
	}
}
