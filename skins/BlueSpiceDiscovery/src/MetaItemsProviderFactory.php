<?php

namespace BlueSpice\Discovery;

use MWStake\MediaWiki\Component\ManifestRegistry\ManifestObjectFactory;

class MetaItemsProviderFactory {

	private const REGISTRY_NAME = 'BlueSpiceDiscoveryMetaItemProviderRegistry';
	private const INSTANCEOF = '\BlueSpice\Discovery\IMetaItemProvider';

	/**
	 * @var ManifestObjectFactory
	 */
	private $objectFactory = null;

	/**
	 * @var array|null
	 */
	private $menus = null;

	/**
	 * @param ManifestObjectFactory $objectFactory
	 */
	public function __construct( ManifestObjectFactory $objectFactory ) {
		$this->objectFactory = $objectFactory;
	}

	/**
	 * @return IMetaItemProvider[]
	 */
	public function getAllMetaItemsProvider(): array {
		if ( $this->menus !== null ) {
			return $this->menus;
		}
		$this->menus = $this->objectFactory->createAllObjects(
			self::REGISTRY_NAME,
			[],
			self::INSTANCEOF
		);

		return $this->menus;
	}

	/**
	 * @param string $name
	 * @return IMetaItemProvider|null
	 */
	public function getMetaItem( string $name ): ?IMetaItemProvider {
		return $this->objectFactory->createObject(
			self::REGISTRY_NAME,
			$name,
			[],
			self::INSTANCEOF
		);
	}
}
