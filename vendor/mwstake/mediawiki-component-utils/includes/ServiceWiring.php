<?php

use MediaWiki\Config\GlobalVarConfig;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\Utils\UtilityFactory;

return [
	'MWStakeCommonUtilsFactory' => static function ( MediaWikiServices $services ) {
		return new UtilityFactory(
			$services
		);
	},
	'MWStakeCommonUtilsConfig' => static function ( MediaWikiServices $services ) {
		return new GlobalVarConfig( 'mwsg' );
	},
];
