<?php

use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\InputProcessor\ProcessorFactory;
use MWStake\MediaWiki\Component\InputProcessor\Runner;

return [
	'MWStake.InputProcessor.Factory' => static function ( MediaWikiServices $services ) {
		$specRegistry = $GLOBALS['mwsgInputProcessorRegistry'] ?? [];
		return new ProcessorFactory(
			$specRegistry,
			$services->getObjectFactory(),
			$services->getHookContainer()
		);
	},
	'MWStake.InputProcessor' => static function ( MediaWikiServices $services ) {
		$logger = LoggerFactory::getInstance( 'mwstake-inputprocessor' );
		return new Runner(
			$services->getService( 'MWStake.InputProcessor.Factory' ),
			$logger
		);
	},
];
