<?php

namespace BlueSpice\Discovery;

use MWStake\MediaWiki\Component\ManifestRegistry\ManifestObjectFactory;

class TemplateDataProviderFactory {

	private const REGISTRY_NAME = 'BlueSpiceDiscoveryTemplateDataProviderRegistry';
	private const INSTANCEOF = '\BlueSpice\Discovery\ITemplateDataProvider';

	/**
	 * @var ManifestObjectFactory
	 */
	private $objectFactory = null;

	/**
	 * @param ManifestObjectFactory $objectFactory
	 */
	public function __construct( ManifestObjectFactory $objectFactory ) {
		$this->objectFactory = $objectFactory;
	}

	/**
	 * @param string $name
	 * @return ITemplateDataProvider|null
	 */
	public function getTemplateDataProvider( string $name ): ?ITemplateDataProvider {
		return $this->objectFactory->createObject(
			self::REGISTRY_NAME,
			$name,
			[],
			self::INSTANCEOF
		);
	}
}
