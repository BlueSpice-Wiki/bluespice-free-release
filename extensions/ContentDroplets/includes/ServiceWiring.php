<?php

use MediaWiki\Extension\ContentDroplets\DropletProvider;
use MediaWiki\Extension\ContentDroplets\DropletSource\RegistrySource;
use MediaWiki\Extension\ContentDroplets\DropletSource\WikiPageSource;
use MediaWiki\MediaWikiServices;
use MediaWiki\Registration\ExtensionRegistry;

return [
	'ContentDropletsProvider' => static function ( MediaWikiServices $services ): DropletProvider {
		return new DropletProvider( [
			'attribute-registry' => new RegistrySource(
				ExtensionRegistry::getInstance()->getAttribute( 'ContentDropletsDroplets' ),
				$services->getObjectFactory()
			),
			'global-var' => new RegistrySource(
				$services->getMainConfig()->get( 'ContentDropletsDroplets' ),
				$services->getObjectFactory()
			),
			'wikipage' => new WikiPageSource(
				$services->getWikiPageFactory()->newFromTitle(
					$services->getTitleFactory()->makeTitle( NS_MEDIAWIKI, 'ContentDroplets.json' )
				)
			),
		] );
	}
];
