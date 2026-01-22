<?php

namespace MWStake\MediaWiki\Component\TokenAuthenticator;

use MediaWiki\User\UserIdentity;

interface MWStakeTokenAuthenticatorGetAuthInfoHook {

	/**
	 * @param UserIdentity $user
	 * @param array &$meta
	 * @return void
	 */
	public function onMWStakeTokenAuthenticatorGetAuthInfo( UserIdentity $user, array &$meta ): void;
}
