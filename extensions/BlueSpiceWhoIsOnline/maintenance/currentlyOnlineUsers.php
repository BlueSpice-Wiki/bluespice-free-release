<?php

use MediaWiki\Maintenance\Maintenance;

require_once dirname( __DIR__, 3 ) . '/maintenance/Maintenance.php';

class CurrentlyOnlineUsers extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Show number of users currently online' );
	}

	/**
	 * @return void
	 */
	public function execute() {
		$tracer = $this->getServiceContainer()->getService( 'BSWhoIsOnlineTracer' );
		$recordSet = $tracer->getTracedRecords();

		$this->output( json_encode( [
			'count' => $recordSet->getTotal(),
		] ) );
	}

}

$maintClass = CurrentlyOnlineUsers::class;
require_once RUN_MAINTENANCE_IF_MAIN;
