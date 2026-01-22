<?php

use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\Wire\WireChannelAuthorizer;
use MWStake\MediaWiki\Component\Wire\WireListenerRegistry;
use MWStake\MediaWiki\Component\Wire\WireMessenger;

return [
	'MWStake.Wire.Messenger' => static function ( MediaWikiServices $services ) {
		return new WireMessenger(
			$services->getHttpRequestFactory(),
			new GlobalVarConfig( 'mwsgWireService' ),
			LoggerFactory::getInstance( 'MWStake.Wire' ),
			new WireListenerRegistry(
				$GLOBALS['mwsgWireListeners'],
				$services->getHookContainer(),
				$services->getObjectFactory()
			)
		);
	},
	'MWStake.Wire.ChannelAuthorizer' => static function ( MediaWikiServices $services ) {
		return new WireChannelAuthorizer(
			$services->getTitleFactory(),
			$services->getSpecialPageFactory(),
			$services->getPermissionManager()
		);
	},
];
