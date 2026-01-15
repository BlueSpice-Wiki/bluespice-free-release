<?php

namespace BlueSpice\Discovery;

use MediaWiki\Config\Config;
use MediaWiki\Config\ConfigFactory;
use MWStake\MediaWiki\Component\CommonUserInterface\IComponent;

class MenuManager {

	/**
	 * @var MenuProviderFactory|null
	 */
	private $menuProviderFactory = null;

	/**
	 * @var ConfigFactory|null
	 */
	protected $configFactory = null;

	/**
	 * @param MenuProviderFactory $menuProviderFactory
	 * @param ConfigFactory $configFactory
	 */
	public function __construct( MenuProviderFactory $menuProviderFactory, ConfigFactory $configFactory ) {
		$this->menuProviderFactory = $menuProviderFactory;
		$this->configFactory = $configFactory;
	}

	/**
	 * @param string $configVarName
	 * @param string $configVarPrefix
	 * @return IComponent|null
	 */
	public function getMenuComponentFromConfigVar(
		string $configVarName,
		string $configVarPrefix = 'bsg'
		): ?IComponent {
		/** @var Config */
		$config = $this->configFactory->makeConfig( $configVarPrefix );

		$value = $config->get( $configVarName );
		if ( !$value ) {
			return null;
		}

		/** @var IMenuProvider */
		$menuProvider = $this->menuProviderFactory->getMenuProvider( $value );
		if ( !$menuProvider ) {
			return null;
		}

		return $menuProvider->getComponent();
	}
}
