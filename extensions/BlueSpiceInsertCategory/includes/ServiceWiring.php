<?php

use BlueSpice\InsertCategory\CategoryManipulatorFactory;
use MediaWiki\MediaWikiServices;

return [
	'BlueSpiceInsertCategory.CategoryManipulatorFactory' => static function ( MediaWikiServices $services ) {
		return new CategoryManipulatorFactory(
			$services->getHookContainer(),
			$services->getObjectFactory()
		);
	}
];
