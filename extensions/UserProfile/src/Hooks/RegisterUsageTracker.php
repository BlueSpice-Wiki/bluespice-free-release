<?php

namespace MediaWiki\Extension\UserProfile\Hooks;

use MediaWiki\Extension\UserProfile\UsageTracker\UsersWithProfileData;

class RegisterUsageTracker {

	/**
	 * @param array &$collectorConfig
	 * @return void
	 */
	public function onBSUsageTrackerRegisterCollectors( array &$collectorConfig ) {
		$collectorConfig['userprofile-number-of-users-with-profile-data'] = [
			'class' => UsersWithProfileData::class,
			'config' => []
		];
	}
}
