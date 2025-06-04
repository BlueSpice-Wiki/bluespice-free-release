<?php

namespace BlueSpice\QrCode\Hook\SetupAfterCache;

class AddFileRepo extends \BlueSpice\Hook\SetupAfterCache {

	/**
	 * @return bool
	 */
	protected function doProcess() {
		global $wgForeignFileRepos;
		$wgForeignFileRepos[] = [
			'class' => \FileRepo::class,
			'name' => 'QrCode',
			'backend' => 'QrCode-backend',
			'directory' => BS_DATA_DIR . '/QrCode/',
			'hashLevels' => 0,
			'url' => BS_DATA_PATH . '/QrCode',
			'scriptDirUrl' => $GLOBALS['wgScriptPath']
		];

		return true;
	}

}
