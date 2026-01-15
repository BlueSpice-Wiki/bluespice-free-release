<?php

use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRecord;
use MWStake\MediaWiki\Component\ManifestRegistry\ManifestRegistryFactory;
use MWStake\MediaWiki\Component\Wikitext\NodeProcessorFactory;
use MWStake\MediaWiki\Component\Wikitext\ParserFactory;

return [
	// TODO: Better name => it can produce any type of parser, for CMs other than wikitext,
	// but calling it ParserFactory collides with MW service
	'MWStakeWikitextParserFactory' => static function ( MediaWikiServices $services ) {
		$processorFactory = $services->getService( 'MWStakeWikitextNodeProcessorRegistryFactory' );
		return new ParserFactory(
			$processorFactory->getAll(),
			$services->getTitleFactory(),
			$services->getRevisionStore(),
			$services->getParser(),
			$services->getSlotRoleRegistry()->getRoleHandler( SlotRecord::MAIN )
		);
	},
	'MWStakeWikitextNodeProcessorRegistryFactory' => static function ( MediaWikiServices $services ) {
		$globalVar = $GLOBALS['mwsgWikitextNodeProcessorRegistry'];

		/** @var ManifestRegistryFactory $manifestAttributeFactory */
		$manifestAttributeFactory = $services->getService( 'MWStakeManifestRegistryFactory' );
		$attributeProcessors = $manifestAttributeFactory->get( 'WikitextComponentNodeProcessors' );

		return new NodeProcessorFactory(
			$globalVar, $attributeProcessors, $services->getObjectFactory()
		);
	}
];
