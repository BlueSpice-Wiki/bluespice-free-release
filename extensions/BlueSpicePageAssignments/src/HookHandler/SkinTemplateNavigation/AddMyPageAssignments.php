<?php

namespace BlueSpice\PageAssignments\HookHandler\SkinTemplateNavigation;

use MediaWiki\Hook\SkinTemplateNavigation__UniversalHook;
use MediaWiki\MediaWikiServices;
use MediaWiki\SpecialPage\SpecialPage;

class AddMyPageAssignments implements SkinTemplateNavigation__UniversalHook {

	/**
	 * // phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName
	 * @inheritDoc
	 */
	public function onSkinTemplateNavigation__Universal( $sktemplate, &$links ): void {
		$user = $sktemplate->getUser();
		if ( !$user->isRegistered() ) {
			return;
		}
		$specialPage = MediaWikiServices::getInstance()->getSpecialPageFactory()->getPage( 'PageAssignments' );
		if ( !$specialPage ) {
			return;
		}

		$links['user-menu']['pageassignments'] = [
			'id' => 'pt-pageassignments',
			'href' => SpecialPage::getTitleFor( 'PageAssignments' )->getLocalURL(),
			'text' => $specialPage->getDescription(),
			'position' => 60,
		];
	}
}
