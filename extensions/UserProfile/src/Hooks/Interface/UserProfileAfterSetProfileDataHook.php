<?php

namespace MediaWiki\Extension\UserProfile\Hooks\Interface;

use MediaWiki\User\User;

interface UserProfileAfterSetProfileDataHook {

	/**
	 * Use to react to the user profile data being saved
	 *
	 * @param array $data
	 * @param User $user
	 * @return void
	 */
	public function onUserProfileAfterSetProfileData( array $data, User $user ): void;

}
