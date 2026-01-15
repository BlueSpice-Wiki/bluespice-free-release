<?php

namespace BlueSpice\Discovery;

use MediaWiki\Config\Config;
use MWStake\MediaWiki\Component\ManifestRegistry\ManifestObjectFactory;

class LangLinksProviderFactory {

	private const REGISTRY_NAME = 'BlueSpiceDiscoveryLangLinksProviderRegistry';
	private const INSTANCEOF = '\BlueSpice\Discovery\ILangLinksProvider';

	/** @var ManifestObjectFactory */
	private $objectFactory = null;

	/** @var string */
	private $providerKey = '';

	/**
	 * @param ManifestObjectFactory $objectFactory
	 * @param Config $config
	 */
	public function __construct( ManifestObjectFactory $objectFactory, Config $config ) {
		$this->objectFactory = $objectFactory;
		$this->providerKey = $config->get( 'DiscoveryLangLinksMode' );
	}

	/**
	 * @return ILangLinksProvider
	 */
	public function create(): ILangLinksProvider {
		return $this->objectFactory->createObject(
			self::REGISTRY_NAME,
			$this->providerKey,
			[],
			self::INSTANCEOF
		);
	}
}
