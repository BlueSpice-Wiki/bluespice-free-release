<?php

namespace BlueSpice\Discovery\HookHandler\SkinTemplateNavigation;

use MediaWiki\Hook\SkinTemplateNavigation__UniversalHook;
use MediaWiki\Title\Title;
use SkinTemplate;
use Throwable;

class ShareOptions implements SkinTemplateNavigation__UniversalHook {

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
		$title = $sktemplate->getSkin()->getTitle();
		if ( $title instanceof Title === false ) {
			return;
		}
		try {
			// `WebRequest::getRequestURL` may fail in some cases (e.g. UnitTests)
			$requestUrl = $sktemplate->getConfig()->get( 'Server' )
				. $sktemplate->getRequest()->getRequestURL();
		} catch ( Throwable $ex ) {
			$requestUrl = $title->getFullURL();
		}

		$subject = $sktemplate->msg( 'bs-discovery-sidebar-secondary-share-sharebymail-subject' );
		$subject->params(
			$sktemplate->getConfig()->get( 'Sitename' ),
			$title->getFullText()
		);
		$body = rawurlencode( $subject->text() );
		$body = $sktemplate->msg( 'bs-discovery-sidebar-secondary-share-sharebymail-body' );
		$body->params(
			$sktemplate->getConfig()->get( 'Sitename' ),
			$title->getFullText(),
			$requestUrl
		);

		$body = rawurlencode( $body->text() );
		$links['actions']['sharebymail'] = [
			'text' => $sktemplate->msg( 'bs-discovery-sidebar-secondary-share-sharebymail-text' ),
			'title' => $sktemplate->msg( 'bs-discovery-sidebar-secondary-share-sharebymail-title' ),
			'href' => "mailto:?subject=$subject&body=$body",
			'class' => 'sharebymail'
		];
	}
}
