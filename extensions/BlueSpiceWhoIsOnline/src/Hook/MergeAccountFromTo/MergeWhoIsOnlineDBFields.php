<?php

namespace BlueSpice\WhoIsOnline\Hook\MergeAccountFromTo;

use BlueSpice\DistributionConnector\Hook\MergeAccountFromTo;

class MergeWhoIsOnlineDBFields extends MergeAccountFromTo {

	protected function doProcess() {
		$utilityFactory = $this->getServices()->getService( 'BSUtilityFactory' );
		$this->getServices()->getDBLoadBalancer()->getConnection( DB_PRIMARY )->update(
			'bs_whoisonline',
			[
				'wo_user_id' => $this->newUser->getId(),
				'wo_user_name' => $this->newUser->getName(),
				'wo_user_real_name' => $utilityFactory->getUserHelper( $this->newUser )
					->getDisplayName()
			],
			[ 'wo_user_id' => $this->oldUser->getId() ],
			__METHOD__
		);
	}

}
