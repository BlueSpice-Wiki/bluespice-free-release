<?php

use MediaWiki\Extension\NotifyMe\Storage\NotificationStore;
use MediaWiki\Extension\NotifyMe\Storage\WebNotificationQueryStore;
use MediaWiki\Maintenance\Maintenance;
use MediaWiki\MediaWikiServices;

require_once __DIR__ . '/../../../maintenance/Maintenance.php';

class UpdateWebNotificationQueryStore extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Update query store for web notitications' );
	}

	public function execute() {
		$this->output( "Clearing query store\n" );

		/** @var WebNotificationQueryStore $queryStore */
		$queryStore = MediaWikiServices::getInstance()->getService( 'NotifyMe.WebQueryStore' );
		$queryStore->clearAll();

		$this->output( "Querying notifications\n" );
		/** @var NotificationStore $notificationStore */
		$notificationStore = MediaWikiServices::getInstance()->getService( 'NotifyMe.Store' );
		// TODO: Batches
		$notifications = $notificationStore->forChannel( 'web' )->query();

		$this->output( "Updating query store for " . count( $notifications ) . " notifications\n" );
		foreach ( $notifications as $notification ) {
			$queryStore->add( $notification );
		}
		$this->output( "Done\n" );
	}
}

$maintClass = UpdateWebNotificationQueryStore::class;
require_once RUN_MAINTENANCE_IF_MAIN;
