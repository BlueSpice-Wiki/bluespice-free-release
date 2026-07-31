<?php
namespace BlueSpice\Authors\Hook\GetDoubleUnderscoreIDs;

class AddNoAuthors extends \BlueSpice\Hook\GetDoubleUnderscoreIDs {

	/**
	 * @return bool
	 */
	protected function doProcess() {
		$this->doubleUnderscoreIDs[] = 'bs_noauthors';
		return true;
	}
}
