<?php

namespace MediaWiki\Extension\ContentDroplets\ResourceLoader;

use MediaWiki\Extension\ContentDroplets\DropletProvider;
use MediaWiki\MediaWikiServices;
use MediaWiki\Registration\ExtensionRegistry;
use MediaWiki\ResourceLoader\Context;
use MediaWiki\ResourceLoader\FileModule;

class DropletDefinitions extends FileModule {

	/** @var array */
	private array $definitions = [];

	/**
	 * @inheritDoc
	 */
	public function getPackageFiles( Context $context ) {
		$this->load();
		$this->packageFiles = [
			[
				'name' => 'dropletDefinitions.json',
				'callback' => function () {
					return $this->definitions;
				},
			],
			"ext.contentdroplets.preload.js"
		];
		return parent::getPackageFiles( $context );
	}

	/**
	 * @return void
	 */
	private function load() {
		/** @var DropletProvider $provider */
		$provider = MediaWikiServices::getInstance()->getService( 'ContentDropletsProvider' );
		$droplets = $provider->getDroplets();
		$this->definitions = [];
		foreach ( $droplets as $key => $droplet ) {
			$this->definitions[$key] = [
				'content' => $droplet->getContent(),
				'rlModules' => $droplet->getRLModules(),
				'veCommand' => $droplet->getVeCommand()
			];
		}
	}

	/**
	 * @param Context|null $context
	 * @return string[]
	 */
	public function getDependencies( ?Context $context = null ) {
		$this->load();
		$this->dependencies = [];
		foreach ( $this->definitions as $droplet ) {
			$this->dependencies = array_merge(
				$this->dependencies,
				$droplet['rlModules']
			);
		}
		if ( ExtensionRegistry::getInstance()->isLoaded( 'BlueSpiceVisualEditorConnector' ) ) {
			array_unshift( $this->dependencies, 'ext.bluespice.visualEditorConnector.tags' );
		}
		return parent::getDependencies( $context );
	}

	/**
	 * Called as ExtensionFunction
	 * @return void
	 */
	public static function addPreloadModules() {
		if ( isset( $GLOBALS[ 'wgVisualEditorPreloadModules' ] ) ) {
			$GLOBALS['wgVisualEditorPreloadModules'][] = 'ext.contentdroplets.bootstrap';
			$GLOBALS['wgVisualEditorPreloadModules'][] = 'z-ext.contentdroplets.definition.preload';
		}
	}
}
