<?php

namespace BlueSpice\Discovery\HookHandler\SkinTemplateNavigation;

use MediaWiki\Hook\SkinTemplateNavigation__UniversalHook;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Title\Title;
use SkinTemplate;

class CreateContentOptions implements SkinTemplateNavigation__UniversalHook {

	/**
	 *
	 * @var PermissionManager
	 */
	private $permissionManager = null;

	/**
	 *
	 * @param PermissionManager $permissionManager
	 */
	public function __construct( PermissionManager $permissionManager ) {
		$this->permissionManager = $permissionManager;
	}

	/**
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
		$title = $sktemplate->getSkin()->getTitle();
		if ( $title instanceof Title === false ) {
			return;
		}

		if ( $title->getContentModel() !== CONTENT_MODEL_WIKITEXT ) {
			return;
		}

		if ( $this->permissionManager->userCan( 'edit', $user, $title ) ) {
			$links['namespaces']['new-section'] = [
				'text' => $sktemplate->msg( 'bs-discovery-create-button-new-section-text' ),
				'title' => $sktemplate->msg( 'bs-discovery-create-button-new-section-title' ),
				'href' => $sktemplate->getTitle()->getLocalURL( [
					'action' => 'edit',
					'section' => 'new'
				] ),
				'class' => 'new-section'
			];
		}
	}
}
