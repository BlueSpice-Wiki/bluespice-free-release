<?php

namespace BlueSpice\HideTitle;

use MediaWiki\MediaWikiServices;
use MediaWiki\Registration\ExtensionRegistry;
use MediaWiki\ResourceLoader\Context as ResourceLoaderContext;
use Wikimedia\ObjectFactory\ObjectFactory;

class CssSelectorModuleFactory {

	/**
	 * @param ResourceLoaderContext $context
	 * @return array
	 */
	public function getModules( $context ): array {
		$moduleRegistry = ExtensionRegistry::getInstance()->getAttribute(
			'BlueSpiceHideTitleCssSelectorModules'
		);

		/** @var ObjectFactory */
		$objectFactory = MediaWikiServices::getInstance()->getObjectFactory();

		$modules = [];
		foreach ( $moduleRegistry as $name => $spec ) {
			$module = $objectFactory->createObject( $spec );

			if ( ( $module instanceof ICssSelectorModule ) === false ) {
				continue;
			}

			if ( $module->skip( $context ) ) {
				continue;
			}

			$modules[] = $module;
		}

		return $modules;
	}
}
