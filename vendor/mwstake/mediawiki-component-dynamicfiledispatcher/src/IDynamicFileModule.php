<?php

namespace MWStake\MediaWiki\Component\DynamicFileDispatcher;

use MediaWiki\Permissions\Authority;
use MediaWiki\User\UserIdentity;

interface IDynamicFileModule {

	/**
	 * @param UserIdentity $user
	 * @param array $params
	 * @return bool
	 */
	public function isAuthorized( Authority $user, array $params ): bool;

	/**
	 * @param array $params
	 * @return IDynamicFile|null
	 */
	public function getFile( array $params ): ?IDynamicFile;
}
