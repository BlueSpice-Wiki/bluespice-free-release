<?php

namespace BlueSpice\SaferEdit\Hook\MergeAccountFromTo;

use BlueSpice\DistributionConnector\Hook\MergeAccountFromTo;

class MergeSaferEditDBFields extends MergeAccountFromTo {

	protected function doProcess() {
		$this->getServices()->getDBLoadBalancer()->getConnection( DB_PRIMARY )->update(
			'bs_saferedit',
			[ 'se_user_name' => $this->newUser->getName() ],
			[ 'se_user_name' => $this->oldUser->getName() ],
			__METHOD__
		);
	}

}
