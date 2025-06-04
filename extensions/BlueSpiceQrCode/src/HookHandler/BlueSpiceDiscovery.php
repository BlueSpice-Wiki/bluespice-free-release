<?php

namespace BlueSpice\QrCode\HookHandler;

use BlueSpice\Discovery\Hook\BlueSpiceDiscoveryTemplateDataProviderAfterInit;
use BlueSpice\Discovery\ITemplateDataProvider;

class BlueSpiceDiscovery implements BlueSpiceDiscoveryTemplateDataProviderAfterInit {

	/**
	 *
	 * @param ITemplateDataProvider $registry
	 * @return void
	 */
	public function onBlueSpiceDiscoveryTemplateDataProviderAfterInit( $registry ): void {
		$registry->unregister( 'actioncollection/actions', 'ca-bs-qr-code' );
		$registry->register( 'panel/share', 'ca-bs-qr-code' );
	}

}
