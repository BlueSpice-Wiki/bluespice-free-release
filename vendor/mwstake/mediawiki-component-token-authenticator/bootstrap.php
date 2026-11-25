<?php

use MediaWiki\Config\ConfigException;
use MWStake\MediaWiki\Component\TokenAuthenticator\CIDRValidator;
use MWStake\MediaWiki\Component\TokenAuthenticator\ServiceTokenSessionProvider;

if ( defined( 'MWSTAKE_MEDIAWIKI_COMPONENT_TOKEN_AUTHENTICATOR_VERSION' ) ) {
	return;
}

define( 'MWSTAKE_MEDIAWIKI_COMPONENT_TOKEN_AUTHENTICATOR_VERSION', '2.1.0' );

MWStake\MediaWiki\ComponentLoader\Bootstrapper::getInstance()
->register( 'token-authenticator', static function () {
	$GLOBALS['wgServiceWiringFiles'][] = __DIR__ . '/ServiceWiring.php';

	// Use this value to sign the token.
	// Same token is set on websocket services to authenticate the token origin
	$GLOBALS['mwsgTokenAuthenticatorSalt'] = $GLOBALS['mwsgTokenAuthenticatorSalt'] ?? '';

	// Default CIDR, used for all tokens not specifying their own and for generating/verifying AppTokens.
	// Must never be empty, for security reasons - service tokens grant far-reaching access,
	// so access to them is always restricted to a network range.
	$GLOBALS['mwsgTokenAuthenticatorServiceCIDR'] =
		$GLOBALS['mwsgTokenAuthenticatorServiceCIDR'] ?? CIDRValidator::DEFAULT_CIDR;

	if ( !CIDRValidator::isValidCIDR( $GLOBALS['mwsgTokenAuthenticatorServiceCIDR'] ) ) {
		throw new ConfigException(
			'$GLOBALS[\'mwsgTokenAuthenticatorServiceCIDR\'] must be set to a valid, non-empty CIDR range ' .
			'(e.g. "127.0.0.1/32"). Disabling the CIDR restriction is not allowed for security reasons.'
		);
	}

	// If you change this value, you are responsible for making sure user is available and is NOT a system user
	$GLOBALS['mwsgTokenAuthenticatorServiceUser'] =
		$GLOBALS['mwsgTokenAuthenticatorServiceUser'] ?? 'Internal service user';

	$GLOBALS['mwsgTokenAuthenticatorServiceTokens'] = $GLOBALS['mwsgTokenAuthenticatorServiceTokens'] ?? [];

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
			'tokens' => $GLOBALS['mwsgTokenAuthenticatorServiceTokens'],
			'main-cidr' => $GLOBALS['mwsgTokenAuthenticatorServiceCIDR']
		] ],
		'services' => [
			'UserFactory',
			'MWStake.TokenAuthenticator.AppAuthenticator',
			'UserGroupManager'
		]
	];
} );
