<?php

use MediaWiki\MediaWikiServices;

if ( defined( 'MWSTAKE_MEDIAWIKI_COMPONENT_WIRE_VERSION' ) ) {
	return;
}

define( 'MWSTAKE_MEDIAWIKI_COMPONENT_WIRE_VERSION', '1.0.9' );

MWStake\MediaWiki\ComponentLoader\Bootstrapper::getInstance()
->register( 'wire', static function () {
	$GLOBALS['wgServiceWiringFiles'][] = __DIR__ . '/ServiceWiring.php';

	// URL for HTTP(s) communication - back channel
	$GLOBALS['mwsgWireServiceUrl'] = $GLOBALS['mwsgWireServiceUrl'] ?? '';
	// URL for WebSocket communication - client
	$GLOBALS['mwsgWireServiceWebsocketUrl'] = $GLOBALS['mwsgWireServiceWebsocketUrl'] ?? '';
	$GLOBALS['mwsgWireServiceAllowInsecureSSL'] = $GLOBALS['mwsgWireServiceAllowInsecureSSL'] ?? false;
	$GLOBALS['mwsgWireServiceApiKey'] = $GLOBALS['mwsgWireServiceApiKey'] ?? '';
	$GLOBALS['mwsgWireListeners'] = $GLOBALS['mwsgWireListeners'] ?? [];

	$GLOBALS['wgExtensionFunctions'][] = static function () {
		$hookContainer = MediaWikiServices::getInstance()->getHookContainer();
		$hookContainer->register( 'BeforePageDisplay', static function ( OutputPage $out ) {
			$out->addModules( [ 'mwstake.component.wire' ] );
			$out->addJsConfigVars( 'mwsgWireServiceWebsocketUrl', $GLOBALS['mwsgWireServiceWebsocketUrl'] );
		} );
	};

	$restFilePath = wfRelativePath( __DIR__ . '/rest-routes.json', $GLOBALS['IP'] );
	$GLOBALS['wgRestAPIAdditionalRouteFiles'][] = $restFilePath;

	$GLOBALS['wgResourceModules']['mwstake.component.wire'] = [
		'scripts' => [
			'resources/bootstrap.js',
			'resources/message.js',
		],
		'dependencies' => [
			'mwstake.component.tokenAuthenticator'
		],
		'localBasePath' => __DIR__
	];
} );
