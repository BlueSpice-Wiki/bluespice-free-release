<?php

// phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName

namespace MediaWiki\Extension\EnhancedUpload\HookHandler;

use MediaWiki\Hook\SkinTemplateNavigation__UniversalHook;
use MediaWiki\Permissions\PermissionManager;

class AddContentActions implements SkinTemplateNavigation__UniversalHook {

	/**
	 * @var PermissionManager
	 */
	private $permissionManager;

	/**
	 * @param PermissionManager $permissionManager
	 */
	public function __construct( PermissionManager $permissionManager ) {
		$this->permissionManager = $permissionManager;
	}

	/**
	 *
	 * @param \SkinTemplate $skinTemplate
	 * @param array &$links
	 */
	public function onSkinTemplateNavigation__Universal( $skinTemplate, &$links ): void {
		$user = $skinTemplate->getUser();
		if ( !$this->permissionManager->userHasRight( $user, 'upload' ) ) {
			return;
		}

		$links['actions']['upload-file'] = [
			'text' => $skinTemplate->getContext()->msg( "enhancedupload-ui-action-new-upload-text" )->text(),
			'title' => $skinTemplate->getContext()->msg( "enhancedupload-ui-action-new-upload-title" )->text(),
			'href' => '#',
			'class' => 'new-file',
			'id' => 'ca-upload-file'
		];
	}
}
