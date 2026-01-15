<?php

use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\CommonWebAPIs\EndpointManager;

return [
	'MWStakeCommonWebAPIsEndpointManager' => static function ( MediaWikiServices $services ) {
		return new EndpointManager( dirname( __DIR__ ) . '/route-files' );
	},
];
