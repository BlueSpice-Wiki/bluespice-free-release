<?php

namespace BlueSpice\SaferEdit\HookHandler;

use BlueSpice\Discovery\ITemplateDataProvider;
use MediaWiki\Config\Config;
use MediaWiki\Config\ConfigFactory;
use MediaWiki\Extension\CollabPads\Hook\CollabPadsAfterAddContentActionHook;
use MediaWiki\Title\Title;
use Wikimedia\Rdbms\LoadBalancer;

class DisableCollabPadsContentAction implements CollabPadsAfterAddContentActionHook {

	/** @var LoadBalancer */
	private $loadBalancer;

	/** @var Config */
	private $bsgConfig;

	/**
	 * @param LoadBalancer $loadBalancer
	 * @param ConfigFactory $configFactory
	 */
	public function __construct( LoadBalancer $loadBalancer, ConfigFactory $configFactory ) {
		$this->loadBalancer = $loadBalancer;
		$this->bsgConfig = $configFactory->makeConfig( 'bsg' );
	}

	/**
	 * @param Title $title
	 * @param ITemplateDataProvider $registry
	 */
	public function onCollabPadsAfterAddContentAction( Title $title, ITemplateDataProvider $registry ): void {
		if ( $this->hasRecentSaferEditSession( $title ) ) {
			// Disable collabpad action during VE
			$registry->unregister( 'panel/edit', 'ca-collabpad' );
		}
	}

	/**
	 * @param Title $title
	 * @return bool
	 */
	protected function hasRecentSaferEditSession( $title ): bool {
		$interval = $this->getInterval();
		$thresholdTS = wfTimestamp( TS_MW, time() - $interval );

		$dbr = $this->loadBalancer->getConnection( DB_REPLICA );
		$res = $dbr->newSelectQueryBuilder()
			->table( 'bs_saferedit' )
			->fields( 'se_id' )
			->where( [
				'se_page_title' => $title->getDBkey(),
				'se_page_namespace' => $title->getNamespace(),
				'se_timestamp > ' . $dbr->addQuotes( $thresholdTS )
			] )
			->caller( __METHOD__ )
			->fetchRowCount();

		return $res > 0;
	}

	/**
	 * @return int
	 */
	protected function getInterval(): int {
		$saferEditInterval = $this->bsgConfig->get( 'SaferEditInterval' );
		$pingInterval = $this->bsgConfig->get( 'PingInterval' );

		return $saferEditInterval + $pingInterval;
	}
}
