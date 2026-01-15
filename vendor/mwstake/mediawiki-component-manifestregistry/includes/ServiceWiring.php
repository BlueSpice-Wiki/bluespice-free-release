<?php

use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use MediaWiki\Registration\ExtensionRegistry;
use MWStake\MediaWiki\Component\ManifestRegistry\ManifestObjectFactory;
use MWStake\MediaWiki\Component\ManifestRegistry\ManifestRegistryFactory;

return [
	'MWStakeManifestRegistryFactory' => static function ( MediaWikiServices $services ) {
		$extensionRegistry = ExtensionRegistry::getInstance();
		$overrides = $GLOBALS['mwsgManifestRegistryOverrides'];
		return new ManifestRegistryFactory( $extensionRegistry, $overrides );
	},
	'MWStakeManifestObjectFactory' => static function ( MediaWikiServices $services ) {
		$logger = LoggerFactory::getInstance( 'MWStakeComponentManifestRegistry' );
		return new ManifestObjectFactory(
			$services->get( 'MWStakeManifestRegistryFactory' ),
			$services->getObjectFactory(),
			$logger
		);
	}
];
