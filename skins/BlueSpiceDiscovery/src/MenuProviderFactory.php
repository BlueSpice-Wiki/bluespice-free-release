<?php

namespace BlueSpice\Discovery;

use MWStake\MediaWiki\Component\ManifestRegistry\ManifestObjectFactory;

class MenuProviderFactory {

	private const REGISTRY_NAME = 'BlueSpiceDiscoveryMenuProviderRegistry';
	private const INSTANCEOF = '\BlueSpice\Discovery\IMenuProvider';

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
	 * @return IMenuProvider[]
	 */
	public function getAllMenuProvider(): array {
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
	 * @return IMenu|null
	 */
	public function getMenuProvider( string $name ): ?IMenuProvider {
		return $this->objectFactory->createObject(
			self::REGISTRY_NAME,
			$name,
			[],
			self::INSTANCEOF
		);
	}
}
