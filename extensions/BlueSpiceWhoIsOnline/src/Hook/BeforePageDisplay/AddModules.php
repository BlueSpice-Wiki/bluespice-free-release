<?php

namespace BlueSpice\WhoIsOnline\Hook\BeforePageDisplay;

use BlueSpice\Hook\BeforePageDisplay;

class AddModules extends BeforePageDisplay {

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		$this->out->addModules( [
			'ext.bluespice.whoisonline',
			'ext.bluespice.whoisonline.styles'
		] );
		return true;
	}

}
