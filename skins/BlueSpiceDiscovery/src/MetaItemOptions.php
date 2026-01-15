<?php

namespace BlueSpice\Discovery;

class MetaItemOptions {

	/**
	 *
	 * @var MetaItemsProviderFactory
	 */
	private $metaItemFactory = null;

	/**
	 *
	 * @param MetaItemsProviderFactory $metaItemFactory
	 */
	public function __construct( $metaItemFactory ) {
		$this->metaItemFactory = $metaItemFactory;
	}

	/**
	 *
	 * @return array
	 */
	public function getOptions() {
		$options = [];

		$metaItemsProviders = $this->metaItemFactory->getAllMetaItemsProvider();
		foreach ( $metaItemsProviders as $metaItemProvider ) {
			$options[] = $metaItemProvider->getName();
		}

		return $options;
	}

}
