<?php

use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\TokenAuthenticator\AppTokenAuthenticator;
use MWStake\MediaWiki\Component\TokenAuthenticator\CIDRValidator;
use MWStake\MediaWiki\Component\TokenAuthenticator\UserTokenAuthenticator;

return [
	'MWStake.TokenAuthenticator.Authenticator' => static function ( MediaWikiServices $services ) {
		$instance = new UserTokenAuthenticator(
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
		$instance->setLogger( $services->getService( 'MWStake.TokenAuthenticator._Logger' ) );
		return $instance;
	},
	'MWStake.TokenAuthenticator.AppAuthenticator' => static function ( MediaWikiServices $services ) {
		$instance = new AppTokenAuthenticator(
			$services->getObjectCacheFactory()->getInstance( $GLOBALS['wgSessionCacheType'] ),
			$GLOBALS['mwsgTokenAuthenticatorSalt']
		);
		$instance->setLogger( $services->getService( 'MWStake.TokenAuthenticator._Logger' ) );
		return $instance;
	},
	'MWStake.TokenAuthenticator._CIDRValidator' => static function () {
		return new CIDRValidator( $GLOBALS['mwsgTokenAuthenticatorServiceCIDR'] );
	},
	'MWStake.TokenAuthenticator._Logger' => static function () {
		return LoggerFactory::getInstance( 'MWStake.TokenAuthenticator' );
	},
];
