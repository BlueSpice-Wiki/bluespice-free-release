<?php

namespace MediaWiki\Extension\UserProfile;

use MediaWiki\Extension\UserProfile\ProfileImage\ProfileImageProviderFactory;
use MediaWiki\MediaWikiServices;

class Extension {

	public static function onRegistration() {
		define( 'SLOT_ROLE_USER_PROFILE', 'user-profile' );
		define( 'CONTENT_MODEL_USER_PROFILE', 'user-profile' );
	}

	/**
	 * @return string
	 */
	public static function getProfileImageProviderRLModules() {
		/** @var ProfileImageProviderFactory $factory */
		$factory = MediaWikiServices::getInstance()->getService( 'UserProfile.ImageProviderFactory' );
		$modules = [];
		foreach ( $factory->getAll() as $provider ) {
			$modules = array_merge( $modules, $provider->getRLModules() );
		}
		return 'ext.userProfile.profileImage.providerModules = ' .
			json_encode( array_values( array_unique( $modules ) ) );
	}
}
