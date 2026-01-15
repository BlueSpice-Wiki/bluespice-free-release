<?php

namespace BlueSpice\Discovery;

use MediaWiki\Config\ConfigFactory;

class MetaItemsManager {

	/**
	 * @var MetaItemsProviderFactory|null
	 */
	private $metaItemsFactory = null;

	/**
	 * @var ConfigFactory|null
	 */
	protected $configFactory = null;

	/**
	 *
	 * @param MetaItemsProviderFactory $metaItemsFactory
	 * @param ConfigFactory $configFactory
	 */
	public function __construct( MetaItemsProviderFactory $metaItemsFactory, ConfigFactory $configFactory ) {
		$this->metaItemsFactory = $metaItemsFactory;
		$this->configFactory = $configFactory;
	}

	/**
	 * @param string $configVarName
	 * @param string $configVarPrefix
	 * @return array
	 */
	public function getMetaItemsFromConfigVar(
		string $configVarName,
		string $configVarPrefix = 'bsg'
		) {
		/** @var Config */
		$config = $this->configFactory->makeConfig( $configVarPrefix );

		$values = $config->get( $configVarName );
		if ( !$values ) {
			return [];
		}
		$metaItemComponents = [];

		foreach ( $values as $value ) {
			/** @var IMetaItemProvider */
			$component = $this->metaItemsFactory->getMetaItem( $value );
			if ( $component ) {
				$metaItemComponents[] = $component->getComponent();
			}
		}

		return $metaItemComponents;
	}
}
