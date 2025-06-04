<?php

use BlueSpice\ExtensionAttributeBasedRegistry;
use MediaWiki\MediaWikiServices;

// PHP unit does not understand code coverage for this file
// as the @covers annotation cannot cover a specific file
// This is fully tested in ServiceWiringTest.php
// @codeCoverageIgnoreStart

return [

	'BSPageAssignmentsAssignmentFactory' => static function ( MediaWikiServices $services ) {
		$assignable = $services->getService(
			'BSPageAssignmentsAssignableFactory'
		);

		$targetRegistry = new ExtensionAttributeBasedRegistry(
			'BlueSpicePageAssignmentsTargetRegistry'
		);

		return new \BlueSpice\PageAssignments\AssignmentFactory(
			$assignable,
			$services->getConfigFactory()->makeConfig( 'bsg' ),
			$targetRegistry
		);
	},

	'BSPageAssignmentsAssignableFactory' => static function ( MediaWikiServices $services ) {
		$registry = new ExtensionAttributeBasedRegistry(
			'BlueSpicePageAssignmentsTypeRegistry'
		);

		return new \BlueSpice\PageAssignments\AssignableFactory(
			$registry,
			$services->getConfigFactory()->makeConfig( 'bsg' )
		);
	},
];

// @codeCoverageIgnoreEnd
