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
		$config = $services->getConfigFactory()->makeConfig( 'bsg' );
		$context = RequestContext::getMain();
		$registry = new ExtensionAttributeBasedRegistry( 'BlueSpiceSaferEditEnvironmentCheckers' );
		$wireMessenger = $services->getService( 'MWStake.Wire.Messenger' );
		return new SaferEditManager( $lb, $context, $config, $registry, $wireMessenger );
	},
];

// @codeCoverageIgnoreEnd
