<?php

namespace BlueSpice\Avatars\AvatarGenerator;

use BlueSpice\Avatars\AvatarGenerator;
use MediaWiki\User\User;

class InstantAvatar extends AvatarGenerator {

	/**
	 * @param User $user
	 * @param int $size
	 * @param array $params
	 * @return string
	 */
	public function generate( User $user, $size, array $params = [] ) {
		// TODO: use composer "vinicius73/laravel-instantavatar"
		$dir = dirname( dirname( __DIR__ ) ) . "/includes/lib/InstantAvatar";
		require_once "$dir/instantavatar.php";

		$instantAvatar = new \InstantAvatar(
			"$dir/Comfortaa-Regular.ttf",
			round( 18 / 40 * $size ),
			$size,
			$size,
			2,
			"$dir/glass.png"
		);

		if ( !empty( $user->getRealName() ) ) {
			preg_match_all(
				'#(^| )(.)#u',
				$user->getRealName(),
				$matches
			);
			$chars = implode( '', $matches[2] );
			if ( mb_strlen( $chars ) < 2 ) {
				$chars = $user->getRealName();
			}
		} else {
			$chars = $user->getName();
		}

		$instantAvatar->generateRandom( $chars );
		return $instantAvatar->getRawPNG();
	}

}
