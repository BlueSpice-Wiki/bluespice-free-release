<?php

namespace BlueSpice\Avatars\AvatarGenerator;

use BlueSpice\Avatars\AvatarGenerator;
use MediaWiki\User\User;

class Identicon extends AvatarGenerator {

	/**
	 *
	 * @param User $user
	 * @param int $size
	 * @param array $params
	 * @return string
	 */
	public function generate( User $user, $size, array $params = [] ) {
		require_once dirname( dirname( __DIR__ ) ) . "/includes/lib/Identicon/identicon.php";
		return generateIdenticon( $user->getId(), $size );
	}

}
