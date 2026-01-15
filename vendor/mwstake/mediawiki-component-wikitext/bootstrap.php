<?php

use MWStake\MediaWiki\Component\Wikitext\NodeProcessor\Header;
use MWStake\MediaWiki\Component\Wikitext\NodeProcessor\Transclusion;

if ( defined( 'MWSTAKE_MEDIAWIKI_COMPONENT_WIKITEXT_VERSION' ) ) {
	return;
}

define( 'MWSTAKE_MEDIAWIKI_COMPONENT_WIKITEXT_VERSION', '7.0.2' );

MWStake\MediaWiki\ComponentLoader\Bootstrapper::getInstance()
	->register( 'wikitext', static function () {
		wfLoadExtension( 'Parsoid', $GLOBALS['IP'] . '/vendor/wikimedia/parsoid/extension.json' );

		$GLOBALS['wgServiceWiringFiles'][] = __DIR__ . '/ServiceWiring.php';

		$GLOBALS['mwsgWikitextNodeProcessorRegistry'] = [
			'transclusion' => [
				'class' => Transclusion::class
			],
			'header' => [
				'class' => Header::class
			]
		];
	} );
