<?php

namespace MediaWiki\Extension\UserProfile\UsageTracker;

use BS\UsageTracker\CollectorResult;
use BS\UsageTracker\Collectors\Base as UsageTrackerBase;
use MediaWiki\Extension\UserProfile\ProfileManager;

class UsersWithProfileData extends UsageTrackerBase {

	/**
	 * @return string
	 */
	public function getDescription() {
		return 'Number of users with profile information';
	}

	/**
	 *
	 * @return string
	 */
	public function getIdentifier() {
		return 'userprofile-number-of-users-with-profile-data';
	}

	/**
	 *
	 * @return CollectorResult
	 */
	public function getUsageData() {
		/** @var ProfileManager $manager */
		$manager = $this->services->getService( 'UserProfile.Manager' );
		$users = $this->services->getDBLoadBalancer()->getConnection( DB_REPLICA )
			->newSelectQueryBuilder()
			->from( 'user' )
			->select( '*' )
			->caller( __METHOD__ )
			->fetchResultSet();

		$hasData = 0;
		foreach ( $users as $user ) {
			$user = $this->services->getUserFactory()->newFromRow( $user );
			if ( !$user->isRegistered() || $user->getBlock() ) {
				continue;
			}
			$data = $manager->getRawProfileData( $user );
			if ( $data ) {
				$hasData++;
			}
		}

		$res = new CollectorResult( $this );
		$res->count = $hasData;
		return $res;
	}
}
