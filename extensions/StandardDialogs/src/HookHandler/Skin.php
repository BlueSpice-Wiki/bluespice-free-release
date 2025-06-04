<?php

namespace MediaWiki\Extension\StandardDialogs\HookHandler;

use MediaWiki\Hook\SkinTemplateNavigation__UniversalHook;
use MediaWiki\MediaWikiServices;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Title\NamespaceInfo;
use MediaWiki\Title\Title;
use SkinTemplate;

class Skin implements SkinTemplateNavigation__UniversalHook {

	/**
	 *
	 * @var PermissionManager
	 */
	private $permissionManager = null;

	/** @var NamespaceInfo */
	private $namespaceInfo = null;

	/**
	 *
	 * @param PermissionManager $permissionManager
	 * @param NamespaceInfo $namespaceInfo
	 */
	public function __construct( PermissionManager $permissionManager, NamespaceInfo $namespaceInfo ) {
		$this->permissionManager = $permissionManager;
		$this->namespaceInfo = $namespaceInfo;
	}

	/**
	 *
	 * @param SkinTemplate $sktemplate
	 * @param array &$links
	 * @return void
	 */
	public function onSkinTemplateNavigation__Universal( $sktemplate, &$links ): void {
		/**
		 * Unfortunately the `VectorTemplateTest::testGetMenuProps` from `Skin:Vector` will break
		 * in `REL1_35`, as it does not properly clear out all hook handlers.
		 * See https://github.com/wikimedia/Vector/blob/1b03bafb1267f350ee2b0018da53c31ee0674f92/tests/phpunit/integration/VectorTemplateTest.php#L107-L108
		 * In later versions this test does not exist anymore and we can remove the bail out again.
		 * We do not perform any own UnitTests on this class, so bailing out here should be fine.
		 */
		if ( defined( 'MW_PHPUNIT_TEST' ) ) {
			return;
		}

		$user = $sktemplate->getSkin()->getUser();
		$title = $sktemplate->getSkin()->getRelevantTitle();
		if ( $title instanceof Title === false ) {
			return;
		}

		if ( $title->isSpecialPage() ) {
			return;
		}

		$sktemplate->getOutput()->addModules( 'ext.standardDialogs' );
		$userCanCreatePages = $this->permissionManager->userHasRight( $user, 'createpage' );

		if ( $userCanCreatePages && $title->exists() && $this->isAllowedContentModel( $title ) ) {
			$links['actions']['copy'] = [
				"href" => '',
				"text" => $sktemplate->msg( 'standarddialogs-copy-page-legend' )->text(),
				"title" => $sktemplate->msg( 'standarddialogs-copy-page-legend' )->text(),
				'position' => 20,
			];
		}

		if ( isset( $links['namespaces']['new-page'] ) ) {
			$links['namespaces']['new-page'] = [
				'text' => $sktemplate->msg( 'standarddialogs-create-button-new-page-text' ),
				'title' => $sktemplate->msg( 'standarddialogs-create-button-new-page-title' ),
				'href' => '',
				'class' => 'new-page'
			];
		}

		if ( $userCanCreatePages && $this->namespaceInfo->hasSubpages( $title->getNamespace() ) ) {
			$links['namespaces']['new-subpage'] = [
				'text' => $sktemplate->msg( 'standarddialogs-create-button-new-subpage-text' ),
				'title' => $sktemplate->msg( 'standarddialogs-create-button-new-subpage-title' ),
				'href' => '',
				'class' => 'new-subpage'
			];
		}
	}

	/**
	 * @param Title $title
	 * @return bool
	 */
	private function isAllowedContentModel( Title $title ): bool {
		$wikiPageFactory = MediaWikiServices::getInstance()->getWikiPageFactory();
		$wikiPage = $wikiPageFactory->newFromTitle( $title );
		$content = $wikiPage->getContent();

		if ( !$content ) {
			return false;
		}

		$model = $content->getModel();
		$allowedModels = [ CONTENT_MODEL_TEXT, CONTENT_MODEL_WIKITEXT ];
		if ( in_array( $model, $allowedModels ) ) {
			return true;
		}

		return false;
	}
}
