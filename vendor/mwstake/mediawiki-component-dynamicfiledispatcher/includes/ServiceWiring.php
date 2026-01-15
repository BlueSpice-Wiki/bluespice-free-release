<?php

return [
	'MWStake.DynamicFileDispatcher.Factory' => static function ( \MediaWiki\MediaWikiServices $services ) {
		$moduleSpecs = $GLOBALS['mwsgMWStakeDynamicFileDispatcherModules'] ?? [];
		$modules = [];
		foreach ( $moduleSpecs as $name => $spec ) {
			$modules[$name] = $services->getObjectFactory()->createObject( $spec );
		}

		return new \MWStake\MediaWiki\Component\DynamicFileDispatcher\DynamicFileDispatcherFactory(
			$modules,
			$services->getHookContainer(),
			$services->getUrlUtils()
		);
	},
];
