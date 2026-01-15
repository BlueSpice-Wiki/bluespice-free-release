<?php

namespace MWStake\MediaWiki\Component\DynamicFileDispatcher\Module;

use MediaWiki\Permissions\Authority;
use MWStake\MediaWiki\Component\DynamicFileDispatcher\File\DefaultGroupImage;
use MWStake\MediaWiki\Component\DynamicFileDispatcher\IDynamicFile;
use MWStake\MediaWiki\Component\DynamicFileDispatcher\IDynamicFileModule;

class GroupImage implements IDynamicFileModule {

	/**
	 * @inheritDoc
	 */
	public function isAuthorized( Authority $user, array $params ): bool {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function getFile( array $params ): ?IDynamicFile {
		return new DefaultGroupImage();
	}
}
