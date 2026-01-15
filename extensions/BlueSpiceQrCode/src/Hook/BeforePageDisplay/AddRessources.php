<?php

namespace BlueSpice\QrCode\Hook\BeforePageDisplay;

class AddRessources extends \BlueSpice\Hook\BeforePageDisplay {

	protected function doProcess() {
		$this->out->addModules( 'ext.qrcode.dialog' );

		return true;
	}
}
