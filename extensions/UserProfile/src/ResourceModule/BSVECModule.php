<?php

namespace MediaWiki\Extension\UserProfile\ResourceModule;

use MediaWiki\Registration\ExtensionRegistry;
use MediaWiki\ResourceLoader\Context;
use MediaWiki\ResourceLoader\FileModule;

class BSVECModule extends FileModule {

	/**
	 * @inheritDoc
	 */
	public function getDependencies( ?Context $context = null ) {
		$dependencies = parent::getDependencies( $context );

		if ( ExtensionRegistry::getInstance()->isLoaded( 'BlueSpiceVisualEditorConnector' ) &&
			!defined( 'MW_PHPUNIT_TEST' ) && !defined( 'MW_QUIBBLE_CI' )
		) {
			$dependencies[] = 'ext.bluespice.visualEditorConnector.tags.classes';
		}

		return $dependencies;
	}
}
