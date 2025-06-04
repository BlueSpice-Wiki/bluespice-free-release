<?php

namespace MediaWiki\Extension\UserProfile\ProfileImage;

use MediaWiki\User\User;
use MediaWiki\User\UserIdentity;

interface IProfileImageProvider {

	/**
	 * Path to the file
	 *
	 * @param UserIdentity $user
	 * @param array $params
	 * @return ProfileImageInfo|null
	 */
	public function provide( UserIdentity $user, array $params = [] ): ?ProfileImageInfo;

	/**
	 * @return array
	 */
	public function getRLModules(): array;

	/**
	 * @param User $user
	 * @return void
	 */
	public function unset( User $user );

	/**
	 * @return int
	 */
	public function getPriority(): int;
}
