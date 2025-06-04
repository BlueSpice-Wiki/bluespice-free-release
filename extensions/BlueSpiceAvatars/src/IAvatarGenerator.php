<?php

namespace BlueSpice\Avatars;

use MediaWiki\User\User;

interface IAvatarGenerator {
	/**
	 * @return string
	 */
	public function getName();

	/**
	 *
	 * @param User $user
	 * @param int $size
	 * @param array $params
	 * @return string
	 */
	public function generate( User $user, $size, array $params = [] );
}
