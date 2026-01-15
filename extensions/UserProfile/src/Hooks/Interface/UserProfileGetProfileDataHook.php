<?php

namespace MediaWiki\Extension\UserProfile\Hooks\Interface;

use MediaWiki\Permissions\Authority;
use MediaWiki\User\User;

interface UserProfileGetProfileDataHook {

	/**
	 * Use to alter data for the user profile when its being retrieved
	 *
	 * @param array &$data
	 * @param User $user
	 * @param Authority $requester
	 * @return void
	 */
	public function onUserProfileGetProfileData( array &$data, User $user, Authority $requester ): void;
}
