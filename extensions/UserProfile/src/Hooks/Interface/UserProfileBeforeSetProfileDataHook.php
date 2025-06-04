<?php

namespace MediaWiki\Extension\UserProfile\Hooks\Interface;

use MediaWiki\Permissions\Authority;
use MediaWiki\User\UserIdentity;

interface UserProfileBeforeSetProfileDataHook {

	/**
	 * Use to alter data for the user profile before its being saved
	 *
	 * @param array &$data
	 * @param UserIdentity $forUser
	 * @param Authority $actor
	 * @return void
	 */
	public function onUserProfileBeforeSetProfileData(
		array &$data, UserIdentity $forUser, Authority $actor
	): void;
}
