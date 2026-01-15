<?php

namespace MediaWiki\Extension\EnhancedUpload\HookHandler;

use BlueSpice\Discovery\Hook\BlueSpiceDiscoveryTemplateDataProviderAfterInit;

class DiscoverySkin implements BlueSpiceDiscoveryTemplateDataProviderAfterInit {

	/**
	 * @inheritDoc
	 */
	public function onBlueSpiceDiscoveryTemplateDataProviderAfterInit( $registry ): void {
		$registry->register( 'panel/create', 'ca-upload-file' );
		$registry->unregister( 'panel/create', 't-new-file' );
		$registry->delete( 'actioncollection/toolbox', 't-upload' );
		$registry->delete( 'actioncollection/toolbox', 't-new-file' );
	}
}
