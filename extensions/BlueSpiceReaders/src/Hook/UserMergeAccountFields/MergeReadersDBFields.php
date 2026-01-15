<?php

namespace BlueSpice\Readers\Hook\UserMergeAccountFields;

use BlueSpice\DistributionConnector\Hook\UserMergeAccountFields;

class MergeReadersDBFields extends UserMergeAccountFields {

	protected function doProcess() {
		$this->updateFields[] = [ 'bs_readers', 'readers_user_id', 'readers_user_name' ];
	}

}
