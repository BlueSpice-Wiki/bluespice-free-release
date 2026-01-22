<?php

use MWStake\MediaWiki\Component\TokenAuthenticator\ServiceTokenSessionProvider;

if ( defined( 'MWSTAKE_MEDIAWIKI_COMPONENT_TOKEN_AUTHENTICATOR_VERSION' ) ) {
	return;
}

define( 'MWSTAKE_MEDIAWIKI_COMPONENT_TOKEN_AUTHENTICATOR_VERSION', '1.1.0' );

MWStake\MediaWiki\ComponentLoader\Bootstrapper::getInstance()
->register( 'token-authenticator', static function () {
	$GLOBALS['wgServiceWiringFiles'][] = __DIR__ . '/ServiceWiring.php';

	// Use this value to sign the token.
	// Same token is set on websocket services to authenticate the token origin
	$GLOBALS['mwsgTokenAuthenticatorSalt'] = $GLOBALS['mwsgTokenAuthenticatorSalt'] ?? '';

	$GLOBALS['mwsgTokenAuthenticatorServiceToken'] = $GLOBALS['mwsgTokenAuthenticatorServiceToken'] ?? '';
	$GLOBALS['mwsgTokenAuthenticatorServiceCIDR'] = $GLOBALS['mwsgTokenAuthenticatorServiceCIDR'] ?? null;
	// If you change this value, you are responsible for making sure user is available and is NOT a system user
	$GLOBALS['mwsgTokenAuthenticatorServiceUser'] =
		$GLOBALS['mwsgTokenAuthenticatorServiceUser'] ?? 'ChatBot service user';

	$GLOBALS['mwsgTokenAuthenticatorServiceAllowedAPIModules'] =
		$GLOBALS['mwsgTokenAuthenticatorServiceAllowedAPIModules'] ?? [];

	$GLOBALS['mwsgTokenAuthenticatorServiceAllowedRestPaths'] =
		$GLOBALS['mwsgTokenAuthenticatorServiceAllowedRestPaths'] ?? [];

	$restFilePath = wfRelativePath( __DIR__ . '/rest-routes.json', $GLOBALS['IP'] );
	$GLOBALS['wgRestAPIAdditionalRouteFiles'][] = $restFilePath;

	$GLOBALS['wgResourceModules']['mwstake.component.tokenAuthenticator'] = [
		'scripts' => [
			'resources/bootstrap.js'
		],
		'localBasePath' => __DIR__
	];

	$GLOBALS['wgHooks']['LoadExtensionSchemaUpdates'][] = static function ( $updater ) {
		$updater->addPostDatabaseUpdateMaintenance(
			\MWStake\MediaWiki\Component\TokenAuthenticator\Maintenance\CreateServiceUser::class
		);
		return true;
	};

	if ( is_string( $GLOBALS['mwsgTokenAuthenticatorServiceUser'] ) ) {
		$GLOBALS['wgReservedUsernames'][] = $GLOBALS['mwsgTokenAuthenticatorServiceUser'];
	}
	$GLOBALS['wgSessionProviders'][ServiceTokenSessionProvider::class] = [
		'class' => ServiceTokenSessionProvider::class,
		'args' => [ [
			'service-user' => $GLOBALS['mwsgTokenAuthenticatorServiceUser'],
			'token' => $GLOBALS['mwsgTokenAuthenticatorServiceToken'],
			'cidr' => $GLOBALS['mwsgTokenAuthenticatorServiceCIDR'],
			"allow-action" => $GLOBALS['mwsgTokenAuthenticatorServiceAllowedAPIModules' ],
			"allow-rest" => $GLOBALS['mwsgTokenAuthenticatorServiceAllowedRestPaths' ],
		] ],
		'services' => [ 'UserFactory' ]
	];
} );
