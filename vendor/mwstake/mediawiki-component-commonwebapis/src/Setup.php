<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs;

use MediaWiki\MediaWikiServices;

class Setup {
	public static function onExtensionFunctions() {
		$endpointManager = MediaWikiServices::getInstance()->getService(
			'MWStakeCommonWebAPIsEndpointManager'
		);
		$endpointManager->enableEndpoints();
	}
}
