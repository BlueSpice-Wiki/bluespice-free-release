<?php

use MediaWiki\MediaWikiServices;
use PageHeader\PageInfoFactory;
use PageHeader\PageInfoSentenceBuilder;
use PageHeader\PageInfoSentenceTypeFactory;

return [

	'PageInfoFactory' => static function ( MediaWikiServices $services ) {
		$registry = $services->getService( 'MWStakeManifestRegistryFactory' )
			->get( 'PageHeaderPageInfoRegistry' );
		return new PageInfoFactory(
			$registry,
			$services->getMainConfig()
		);
	},

	'PageInfoSentenceTypeFactory' => static function ( MediaWikiServices $services ) {
		$registry = $services->getService( 'MWStakeManifestRegistryFactory' )
			->get( 'PageHeaderPageInfoSentenceTypeRegistry' );
		return new PageInfoSentenceTypeFactory(
			$registry,
			$services->getMainConfig()
		);
	},

	'PageInfoSentenceBuilder' => static function ( MediaWikiServices $services ) {
		return new PageInfoSentenceBuilder(
			$services->getService( 'PageInfoFactory' ),
			$services->getService( 'PageInfoSentenceTypeFactory' )
		);
	},

];
