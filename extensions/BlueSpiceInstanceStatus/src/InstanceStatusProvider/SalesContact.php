<?php

namespace BlueSpice\InstanceStatus\InstanceStatusProvider;

use BlueSpice\InstanceStatus\IStatusProvider;
use MediaWiki\Config\Config;
use MediaWiki\Config\ConfigFactory;
use MediaWiki\Message\Message;

class SalesContact implements IStatusProvider {
	/** @var Config */
	private $config;

	/**
	 * @param ConfigFactory $configFactory
	 */
	public function __construct( ConfigFactory $configFactory ) {
		$this->config = $configFactory->makeConfig( 'bsg' );
	}

	/**
	 * @return string
	 */
	public function getLabel(): string {
		return Message::newFromKey( 'bs-instancestatus-instance-status-sales-contact-label' )->text();
	}

	/**
	 * @return string
	 */
	public function getValue(): string {
		return Message::newFromKey( 'bs-instancestatus-instance-status-sales-contact' )
			->params( $this->config->get( 'InstanceStatusBSContactLink' ) )
			->parse();
	}

	/**
	 * @return string
	 */
	public function getIcon(): string {
		return 'message';
	}

	/**
	 * @return int
	 */
	public function getPriority(): int {
		return 60;
	}
}
