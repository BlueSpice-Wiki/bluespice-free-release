<?php

namespace MediaWiki\Extension\EnhancedUpload\Migration;

use MediaWiki\Maintenance\LoggedUpdateMaintenance;
use Wikimedia\Rdbms\DBConnRef;

class MigrateConfigSettings extends LoggedUpdateMaintenance {

	/** @var array */
	private const CONFIGS = [
		'EnhancedUploadDragDropImagesHeight',
		'EnhancedUploadDragDropImagesWidth',
	];

	/**
	 * @inheritDoc
	 */
	protected function doDBUpdates() {
		/** @var DBConnRef $dbw */
		$dbw = $this->getPrimaryDB();

		if ( !$dbw->tableExists( 'bs_settings3', __METHOD__ ) ) {
			return false;
		}

		$dbw->newDeleteQueryBuilder()
			->table( 'bs_settings3' )
			->where( [
				's_name' => self::CONFIGS,
				's_value' => '"auto"',
			] )
			->caller( __METHOD__ )
			->execute();

		return true;
	}

	/**
	 * @inheritDoc
	 */
	protected function getUpdateKey() {
		return 'EnhancedUpload::MigrateConfigSettings';
	}

}
