<?php

use BlueSpice\InstanceStatus\StatusReport;

require_once dirname( __DIR__, 3 ) . '/maintenance/Maintenance.php';

class StatusCheck extends \MediaWiki\Maintenance\Maintenance {

	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Display status of the wiki' );
	}

	/**
	 * @return void
	 */
	public function execute() {
		/** @var StatusReport $factory */
		$factory = $this->getServiceContainer()->getService( 'BlueSpiceInstanceStatus.Report' );
		$this->output( json_encode( $factory->getReportForAPI(), JSON_PRETTY_PRINT ) );
	}
}

$maintClass = StatusCheck::class;
require_once RUN_MAINTENANCE_IF_MAIN;
