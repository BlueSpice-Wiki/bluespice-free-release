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
		$db = $services->getDBLoadBalancer()->getConnection( DB_PRIMARY );
		$context = RequestContext::getMain();
		$registry = new ExtensionAttributeBasedRegistry( 'BlueSpiceSaferEditEnvironmentCheckers' );
		return new SaferEditManager( $db, $context, $registry );
	},
];

// @codeCoverageIgnoreEnd
