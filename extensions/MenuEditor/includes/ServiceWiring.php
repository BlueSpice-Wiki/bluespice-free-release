<?php

use MediaWiki\Extension\MenuEditor\MenuAttributeRegistry;
use MediaWiki\Extension\MenuEditor\MenuFactory;
use MediaWiki\MediaWikiServices;

return [
	'MenuEditorMenuFactory' => static function ( MediaWikiServices $services ): MenuFactory {
		return new MenuFactory(
			new MenuAttributeRegistry( 'MenuEditorMenus' ),
			$services->getObjectFactory()
		);
	}
];
