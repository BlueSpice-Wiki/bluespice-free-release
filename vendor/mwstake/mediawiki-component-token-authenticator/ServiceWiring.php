<?php

use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\TokenAuthenticator\AppTokenAuthenticator;
use MWStake\MediaWiki\Component\TokenAuthenticator\CIDRValidator;
use MWStake\MediaWiki\Component\TokenAuthenticator\UserTokenAuthenticator;

return [
	'MWStake.TokenAuthenticator.Authenticator' => static function ( MediaWikiServices $services ) {
		return new UserTokenAuthenticator(
			$services->getUrlUtils(),
			$services->getObjectCacheFactory()->getInstance( $GLOBALS['wgSessionCacheType'] ),
			$services->getUserFactory(),
			$services->getUserGroupManager(),
			$services->getUserOptionsLookup(),
			$services->getLanguageNameUtils(),
			$services->getContentLanguage(),
			$services->getHookContainer(),
			$GLOBALS['mwsgTokenAuthenticatorSalt']
		);
	},
	'MWStake.TokenAuthenticator.AppAuthenticator' => static function ( MediaWikiServices $services ) {
		return new AppTokenAuthenticator(
			$services->getObjectCacheFactory()->getInstance( $GLOBALS['wgSessionCacheType'] ),
			$GLOBALS['mwsgTokenAuthenticatorSalt']
		);
	},
	'MWStake.TokenAuthenticator._CIDRValidator' => static function () {
		return new CIDRValidator( $GLOBALS['mwsgTokenAuthenticatorServiceCIDR'] );
	}
];
