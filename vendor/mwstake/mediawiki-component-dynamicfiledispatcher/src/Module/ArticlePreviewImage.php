<?php

namespace MWStake\MediaWiki\Component\DynamicFileDispatcher\Module;

use MediaWiki\Permissions\Authority;
use MWStake\MediaWiki\Component\DynamicFileDispatcher\IDynamicFile;
use MWStake\MediaWiki\Component\DynamicFileDispatcher\IDynamicFileModule;

class ArticlePreviewImage implements IDynamicFileModule {

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
		return new DefaultArticlePreviewImage();
	}
}
