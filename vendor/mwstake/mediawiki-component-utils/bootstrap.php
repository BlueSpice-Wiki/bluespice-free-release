<?php

if ( defined( 'MWSTAKE_MEDIAWIKI_COMPONENT_UTILS_VERSION' ) ) {
	return;
}

define( 'MWSTAKE_MEDIAWIKI_COMPONENT_UTILS_VERSION', '3.0.1' );

MWStake\MediaWiki\ComponentLoader\Bootstrapper::getInstance()
->register( 'utils', static function () {
	/*
	 * Used by extensions like `BlueSpiceGroupManager` to flag custom groups
	 * Do not fill this array elsewhere, otherwise those extensions will get
	 * confused!
	 */
	$GLOBALS['wgAdditionalGroups'] = [];

	/*
	 * Used to filter groups according to types.
	 * Possible types are
	 * - implicit: Groups that are assigned by the system, such as `*` and `user`
	 * - core-minimal: MediaWiki groups that are needed for a proper rights setup
	 * - core-extended: MediaWiki groups that should not be used in the interface
	 * - extension-minimal: Groups by extensions that are needed for a proper rights setup
	 * - extension-extended: Groups by exensions that should not be used in the interface
	 * - custom: Groups that are set up by an administrator locally
	 */
	$GLOBALS['wgGroupTypes'] = [];

	$GLOBALS['wgServiceWiringFiles'][] = __DIR__ . '/includes/ServiceWiring.php';
} );
