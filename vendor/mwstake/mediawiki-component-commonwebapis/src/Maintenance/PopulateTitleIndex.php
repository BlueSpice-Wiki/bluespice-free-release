<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs\Maintenance;

use MediaWiki\Maintenance\LoggedUpdateMaintenance;

$maintPath = dirname( __DIR__, 5 ) . '/maintenance/Maintenance.php';
if ( file_exists( $maintPath ) ) {
	require_once $maintPath;
}

class PopulateTitleIndex extends LoggedUpdateMaintenance {
	/**
	 * @return bool
	 */
	public function doDBUpdates() {
		$db = $this->getDB( DB_PRIMARY );
		$db->delete( 'mws_title_index', '*', __METHOD__ );

		$titles = $db->select(
			[ 'p' => 'page', 'pp' => 'page_props' ],
			[ 'page_id', 'page_namespace', 'page_title', 'pp_value' ],
			[],
			__METHOD__,
			[],
			[ 'pp' => [ 'LEFT OUTER JOIN', [ 'p.page_id = pp.pp_page', 'pp.pp_propname' => 'displaytitle' ] ] ]
		);

		$toInsert = [];
		$cnt = 0;
		$batch = 250;
		foreach ( $titles as $title ) {
			$toInsert[] = [
				'mti_page_id' => $title->page_id,
				'mti_namespace' => mb_strtolower( $title->page_namespace ),
				'mti_title' => mb_strtolower( str_replace( '_', ' ', $title->page_title ) ),
				'mti_displaytitle' => mb_strtolower( str_replace( '_', ' ', $title->pp_value ?? '' ) ),
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

		$this->output( "Indexed $cnt pages\n" );

		return true;
	}

	/**
	 * @param array $batch
	 */
	private function insertBatch( array $batch ) {
		$db = $this->getDB( DB_PRIMARY );
		$db->insert(
			'mws_title_index',
			$batch,
			__METHOD__,
			[ 'IGNORE' ]
		);
	}

	/**
	 * @return string
	 */
	protected function getUpdateKey() {
		return 'mws-title-index-init';
	}
}

$maintClass = PopulateTitleIndex::class;
require_once RUN_MAINTENANCE_IF_MAIN;
