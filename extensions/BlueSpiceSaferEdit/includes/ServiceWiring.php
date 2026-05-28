<?php

use BlueSpice\ExtensionAttributeBasedRegistry;
use BlueSpice\SaferEdit\SaferEditManager;
use MediaWiki\MediaWikiServices;

// PHP unit does not understand code coverage for this file
// as the @covers annotation cannot cover a specific file
// This is fully tested in ServiceWiringTest.php
// @codeCoverageIgnoreStart

return [
	'BSSaferEditManager' => static function ( MediaWikiServices $services ) {
		$lb = $services->getDBLoadBalancer();
		$context = RequestContext::getMain();
		$registry = new ExtensionAttributeBasedRegistry( 'BlueSpiceSaferEditEnvironmentCheckers' );
		$permissionManager = $services->getPermissionManager();
		return new SaferEditManager( $lb, $context, $registry, $permissionManager );
	},
];

// @codeCoverageIgnoreEnd
