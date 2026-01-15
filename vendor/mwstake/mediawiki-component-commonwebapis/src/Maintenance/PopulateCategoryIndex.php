<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs\Maintenance;

use MediaWiki\Maintenance\LoggedUpdateMaintenance;

$maintPath = dirname( __DIR__, 5 ) . '/maintenance/Maintenance.php';
if ( file_exists( $maintPath ) ) {
	require_once $maintPath;
}
class PopulateCategoryIndex extends LoggedUpdateMaintenance {
	/**
	 * @return bool
	 */
	public function doDBUpdates() {
		$db = $this->getDB( DB_PRIMARY );
		$db->delete( 'mws_category_index', '*', __METHOD__ );

		$links = $db->select(
			'category',
			[ 'cat_id', 'cat_title', 'cat_pages' ],
			[],
			__METHOD__
		);

		$toInsert = [];
		$cnt = 0;
		$batch = 250;
		foreach ( $links as $link ) {
			$toInsert[] = [
				'mci_cat_id' => $link->cat_id,
				'mci_title' => mb_strtolower( str_replace( '_', ' ', $link->cat_title ) ),
				'mci_page_title' => $link->cat_title,
				'mci_count' => $link->cat_pages
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

		$this->output( "Indexed $cnt categories\n" );

		return true;
	}

	/**
	 * @param array $batch
	 */
	private function insertBatch( array $batch ) {
		$db = $this->getDB( DB_PRIMARY );
		$db->insert(
			'mws_category_index',
			$batch,
			__METHOD__,
			[ 'IGNORE' ]
		);
	}

	/**
	 * @return string
	 */
	protected function getUpdateKey() {
		return 'mws-category-index-init';
	}
}

$maintClass = PopulateCategoryIndex::class;
require_once RUN_MAINTENANCE_IF_MAIN;
