<?php

namespace MWStake\MediaWiki\Component\AlertBanners\AlertProviderFactory;

use MediaWiki\HookContainer\HookContainer;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\AlertBanners\ObjectFactory;

class Hook extends Base {

	/**
	 * @var HookContainer
	 */
	private $hookContainer = null;

	/**
	 *
	 * @param ObjectFactory $objectFactory
	 * @param HookContainer $hookContainer
	 */
	public function __construct( $objectFactory, $hookContainer ) {
		parent::__construct( $objectFactory );
		$this->hookContainer = $hookContainer;
	}

	/**
	 * @inheritDoc
	 */
	public function processProviders( $providers ) {
		$handlerSpecs = [];
		$this->hookContainer = MediaWikiServices::getInstance()->getHookContainer();
		$this->hookContainer->run( 'MWStakeAlertBannersRegisterProviders', [ &$handlerSpecs ] );
		foreach ( $handlerSpecs as $handlerId => $handlerSpec ) {
			$providers[$handlerId] = $this->objectFactory->createObject( $handlerSpec );
		}

		return $providers;
	}
}
