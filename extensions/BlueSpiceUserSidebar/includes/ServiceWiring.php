<?php

use BlueSpice\UserSidebar\Parser;
use BlueSpice\UserSidebar\WidgetFactory;
use MediaWiki\MediaWikiServices;

// PHP unit does not understand code coverage for this file
// as the @covers annotation cannot cover a specific file
// This is fully tested in ServiceWiringTest.php
// @codeCoverageIgnoreStart

return [

	'BSUserSidebarWidgetFactory' => static function ( MediaWikiServices $services ) {
		$registry = $services->getService( 'MWStakeManifestRegistryFactory' )
			->get( 'BlueSpiceUserSidebarWidgetRegistry' );
		return new WidgetFactory(
			$registry,
			$services->getMainConfig()
		);
	},

	'BSUserSidebarParser' => static function ( MediaWikiServices $services ) {
		return new Parser(
			$services->getService( 'BSUserSidebarWidgetFactory' ),
			$services->getTitleFactory()
		);
	},

];

// @codeCoverageIgnoreEnd
