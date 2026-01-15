<?php

namespace BlueSpice\Discovery;

use MediaWiki\MediaWikiServices;

class MenuSelectorOptions {

	/**
	 * @var MenuProviderFactory
	 */
	private $menuProviderFactory = null;

	public function __construct() {
		$services = MediaWikiServices::getInstance();
		$menuProviderFactory = $services->getService( 'BlueSpiceDiscoveryMenuProviderFactory' );
		$this->menuProviderFactory = $menuProviderFactory;
	}

	/**
	 * @return array
	 */
	public function getOptions(): array {
		$options = [];
		$menuProviders = $this->menuProviderFactory->getAllMenuProvider();

		foreach ( $menuProviders as $menuProvider ) {
			$options[$menuProvider->getLabelMsg()->text()] = $menuProvider->getName();
		}

		return $options;
	}
}
