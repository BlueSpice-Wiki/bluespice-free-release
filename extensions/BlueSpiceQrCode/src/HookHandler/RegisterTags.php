<?php

namespace BlueSpice\QrCode\HookHandler;

use BlueSpice\QrCode\Tag\QrCode;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Title\TitleFactory;
use MWStake\MediaWiki\Component\GenericTagHandler\Hook\MWStakeGenericTagHandlerInitTagsHook;

class RegisterTags implements MWStakeGenericTagHandlerInitTagsHook {

	/**
	 * @param TitleFactory $titleFactory
	 * @param PermissionManager $permissionManager
	 */
	public function __construct(
		private readonly TitleFactory $titleFactory,
		private readonly PermissionManager $permissionManager
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function onMWStakeGenericTagHandlerInitTags( array &$tags ) {
		$tags[] = new QrCode( $this->titleFactory, $this->permissionManager );
	}
}
