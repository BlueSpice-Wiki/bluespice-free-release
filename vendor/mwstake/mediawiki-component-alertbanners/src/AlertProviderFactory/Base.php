<?php

namespace MWStake\MediaWiki\Component\AlertBanners\AlertProviderFactory;

use Exception;
use MWStake\MediaWiki\Component\AlertBanners\IAlertProvider;
use MWStake\MediaWiki\Component\AlertBanners\IAlertProviderFactory;
use Wikimedia\ObjectFactory\ObjectFactory;

abstract class Base implements IAlertProviderFactory {

	/** @var ObjectFactory */
	protected $objectFactory;

	/** @var IAlertProvider */
	protected $currentAlertProvider;

	/**
	 * @param ObjectFactory $objectFactory
	 */
	public function __construct( $objectFactory ) {
		$this->objectFactory = $objectFactory;
	}

	/**
	 * @param string $regKey
	 * @throws Exception
	 */
	protected function checkHandlerInterface( $regKey ) {
		$doesImplementInterface =
			$this->currentAlertProvider instanceof IAlertProvider;

		if ( !$doesImplementInterface ) {
			throw new Exception(
				"Provider factory '$regKey' did not return "
					. "'IAlertProvider' instance!"
			);
		}
	}
}
