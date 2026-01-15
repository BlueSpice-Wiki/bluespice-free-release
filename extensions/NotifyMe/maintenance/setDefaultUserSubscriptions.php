<?php

use MediaWiki\Maintenance\Maintenance;
use MediaWiki\MediaWikiServices;

require_once __DIR__ . '/../../../maintenance/Maintenance.php';

class SetDefaultUserSubscriptions extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Set default user subscriptions' );
		$this->addOption( 'json', 'JSON string', false, true );
		$this->addArg( 'json-file', 'Path to the JSON file', false );
	}

	public function execute() {
		if ( $this->hasOption( 'json' ) ) {
			$json = $this->getOption( 'json' );
		} else {
			$file = $this->getArg( 0 );
			if ( !$file || !file_exists( $file ) ) {
				$this->output( "File not found\n" );
				return;
			}

			$json = file_get_contents( $file );
		}

		$data = json_decode( $json, true );
		if ( !$data ) {
			$this->output( "Invalid JSON\n" );
			return;
		}

		$db = $this->getDB( DB_PRIMARY );
		$res = $db->select(
			[ 'user_properties', 'user' ],
			[ 'user_id' ],
			[
				'up_user = user_id',
				'up_property' => 'ext-notification-subscriptions',
			],
			__METHOD__
		);
		foreach ( $res as $row ) {
			$user = MediaWikiServices::getInstance()->getUserFactory()->newFromId( $row->user_id );
			MediaWikiServices::getInstance()->getUserOptionsManager()->setOption(
				$user, 'ext-notification-subscriptions', $json
			);
			MediaWikiServices::getInstance()->getUserOptionsManager()->saveOptions( $user );
			$this->output( "Set default subscriptions for " . $user->getName() . "\n" );
		}
	}
}

$maintClass = SetDefaultUserSubscriptions::class;
require_once RUN_MAINTENANCE_IF_MAIN;
