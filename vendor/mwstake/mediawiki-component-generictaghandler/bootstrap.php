<?php

if ( defined( 'MWSTAKE_MEDIAWIKI_COMPONENT_GENERICTAGHANDLER_VERSION' ) ) {
	return;
}

define( 'MWSTAKE_MEDIAWIKI_COMPONENT_GENERICTAGHANDLER_VERSION', '1.0.4' );

MWStake\MediaWiki\ComponentLoader\Bootstrapper::getInstance()
	->register( 'generictaghandler', static function () {
		$GLOBALS['wgServiceWiringFiles'][] = __DIR__ . '/includes/ServiceWiring.php';
		$GLOBALS['wgMessagesDirs']['mwstake-component-generictaghandler'] = __DIR__ . '/i18n';

		$GLOBALS['wgHooks']['ParserFirstCallInit'][] = static function ( \Parser $parser ) {
			if ( MW_ENTRY_POINT === 'load' || defined( 'MW_QUIBBLE_CI' ) ) {
				return true;
			}
			$services = \MediaWiki\MediaWikiServices::getInstance();
			/** @var \MWStake\MediaWiki\Component\GenericTagHandler\TagFactory $factory */
			$factory = $services->getService( 'MWStake.GenericTagHandler.TagFactory' );
			$tags = $factory->getAll();
			foreach ( $tags as $tag ) {
				$renderer = $factory->makeTagRendererForTag( $tag );
				$tagNames = $tag->getTagNames();
				foreach ( $tagNames as $tagName ) {
					$parser->setHook( $tagName, [ $renderer, 'doRender' ] );
				}
			}

			return true;
		};
		$GLOBALS['mwsgGenericTagRegistry'] = [];

		$restFilePath = wfRelativePath( __DIR__ . '/rest-routes.json', $GLOBALS['IP'] );
		$GLOBALS['wgRestAPIAdditionalRouteFiles'][] = $restFilePath;
	} );
