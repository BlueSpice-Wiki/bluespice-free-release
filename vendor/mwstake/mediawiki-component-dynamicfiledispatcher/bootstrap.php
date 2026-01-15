<?php

use MWStake\MediaWiki\ComponentLoader\Bootstrapper;

if ( defined( 'MWSTAKE_MEDIAWIKI_COMPONENT_DYNAMICFILEDISPATCHER_VERSION' ) ) {
	return;
}

define( 'MWSTAKE_MEDIAWIKI_COMPONENT_DYNAMICFILEDISPATCHER_VERSION', '1.0.1' );

Bootstrapper::getInstance()
	->register( 'dynamicFileDispatcher', static function () {
		$GLOBALS['wgServiceWiringFiles'][] = __DIR__ . '/includes/ServiceWiring.php';

		$GLOBALS['mwsgMWStakeDynamicFileDispatcherModules'] = [
			'articlepreviewimage' => [
				'class' => \MWStake\MediaWiki\Component\DynamicFileDispatcher\Module\ArticlePreviewImage::class
			],
			'groupimage' => [
				'class' => \MWStake\MediaWiki\Component\DynamicFileDispatcher\Module\GroupImage::class
			],
			'userprofileimage' => [
				'class' => \MWStake\MediaWiki\Component\DynamicFileDispatcher\Module\UserProfileImage::class
			],
		];

		$GLOBALS['wgRestAPIAdditionalRouteFiles'][] = wfRelativePath( __DIR__ . '/route.json', $GLOBALS['IP'] );

		$GLOBALS['wgResourceModules']['ext.mws.dynamicFileDispatcher'] = [
			'scripts' => [
				'bootstrap.js'
			],
			'localBasePath' => __DIR__ . '/resources'
		];
	} );
