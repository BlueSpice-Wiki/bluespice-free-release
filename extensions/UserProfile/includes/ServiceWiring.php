<?php

use MediaWiki\Extension\UserProfile\ProfileFieldRegistry;
use MediaWiki\Extension\UserProfile\ProfileImage\IProfileImageProvider;
use MediaWiki\Extension\UserProfile\ProfileImage\ProfileImageProviderFactory;
use MediaWiki\Extension\UserProfile\ProfileManager;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use MediaWiki\Registration\ExtensionRegistry;

return [
	'UserProfile.Manager' => static function ( MediaWikiServices $services ) {
		return new ProfileManager(
			$services->getHookContainer(),
			$services->getPermissionManager(),
			$services->getUserFactory(),
			$services->getWikiPageFactory(),
			$services->getService( 'UserProfile.FieldRegistry' ),
			LoggerFactory::getInstance( 'UserProfile.Manager' )
		);
	},
	'UserProfile.FieldRegistry' => static function ( MediaWikiServices $services ) {
		return new ProfileFieldRegistry(
			ExtensionRegistry::getInstance()->getAttribute( 'UserProfileFields' ),
			$services->getMainConfig(),
			$services->getUserOptionsLookup()
		);
	},
	'UserProfile.ImageProviderFactory' => static function ( MediaWikiServices $services ) {
		$instance = new ProfileImageProviderFactory();
		$attr = ExtensionRegistry::getInstance()->getAttribute( 'UserProfileImageProviders' );
		foreach ( $attr as $providerName => $spec ) {
			$object = $services->getObjectFactory()->createObject( $spec );
			if ( !$object instanceof IProfileImageProvider ) {
				throw new RuntimeException( "Invalid object for profile image provider: $providerName" );
			}
			$instance->registerProvider( $providerName, $object );
		}
		return $instance;
	},
];
