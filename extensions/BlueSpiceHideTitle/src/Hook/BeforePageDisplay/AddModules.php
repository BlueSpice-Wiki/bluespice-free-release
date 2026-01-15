<?php

namespace BlueSpice\HideTitle\Hook\BeforePageDisplay;

use BlueSpice\Hook\BeforePageDisplay;
use MediaWiki\MediaWikiServices;

class AddModules extends BeforePageDisplay {

	/**
	 * @return bool
	 */
	protected function skipProcessing() {
		$hideTitlePageProp = MediaWikiServices::getInstance()->getPageProps()
			->getProperties( $this->out->getTitle(), 'bs_hidetitle' );

		if ( empty( $hideTitlePageProp ) ) {
			return true;
		}
		return false;
	}

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		$this->out->addModuleStyles( 'ext.bluespice.hidetitle.styles' );
		return true;
	}

}
