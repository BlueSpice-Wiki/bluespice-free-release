<?php

use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\GenericTagHandler\TagFactory;

return [

	/**
	 * @param MediaWikiServices $services
	 */
	'MWStake.GenericTagHandler.TagFactory' => static function ( $services ) {
		return new TagFactory(
			$services->getHookContainer(),
			new \MediaWiki\Config\GlobalVarConfig( 'mwsg' ),
			$services->getObjectFactory(),
			$services
		);
	}
];
