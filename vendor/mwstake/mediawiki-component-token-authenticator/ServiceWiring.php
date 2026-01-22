<?php

use MediaWiki\MediaWikiServices;
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
];
