<?php

namespace BlueSpice\PageAssignments\Hook\MergeAccountFromTo;

use BlueSpice\DistributionConnector\Hook\MergeAccountFromTo;

class MergePageAssignmentsDBFields extends MergeAccountFromTo {

	protected function doProcess() {
		$this->getServices()->getDBLoadBalancer()->getConnection( DB_PRIMARY )->update(
			'bs_pageassignments',
			[ 'pa_assignee_key' => $this->newUser->getName() ],
			[ 'pa_assignee_key' => $this->oldUser->getName(), 'pa_assignee_type' => 'user' ],
			__METHOD__
		);
	}

}
