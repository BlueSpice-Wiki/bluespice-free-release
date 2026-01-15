<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs\Maintenance;

use MediaWiki\Maintenance\LoggedUpdateMaintenance;

$maintPath = dirname( __DIR__, 5 ) . '/maintenance/Maintenance.php';
if ( file_exists( $maintPath ) ) {
	require_once $maintPath;
}
class PopulateUserIndex extends LoggedUpdateMaintenance {
	/**
	 * @return bool
	 */
	protected function doDBUpdates() {
		$db = $this->getDB( DB_REPLICA );

		$users = $db->select(
			'user',
			[ 'user_id', 'user_name', 'user_real_name' ],
			[],
			__METHOD__
		);

		$toInsert = [];
		$cnt = 0;
		$batch = 250;
		foreach ( $users as $user ) {
			$toInsert[] = [
				'mui_user_id' => $user->user_id,
				'mui_user_name' => mb_strtolower( $user->user_name ),
				'mui_user_real_name' => mb_strtolower( $user->user_real_name )
			];
			if ( $cnt % $batch === 0 ) {
				$this->insertBatch( $toInsert );
				$toInsert = [];
			}
			$cnt++;
		}
		if ( !empty( $toInsert ) ) {
			$this->insertBatch( $toInsert );
		}

		$this->output( "Indexed $cnt users\n" );

		return true;
	}

	/**
	 * @param array $batch
	 *
	 * @return void
	 */
	private function insertBatch( array $batch ) {
		$db = $this->getDB( DB_PRIMARY );
		$db->insert(
			'mws_user_index',
			$batch,
			__METHOD__,
			[ 'IGNORE' ]
		);
	}

	/**
	 * @return string
	 */
	protected function getUpdateKey() {
		return 'mws-user-index-init';
	}
}

$maintClass = PopulateUserIndex::class;
require_once RUN_MAINTENANCE_IF_MAIN;
