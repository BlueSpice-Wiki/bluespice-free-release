<?php

namespace BlueSpice\Readers\HookHandler;

use MediaWiki\Hook\SkinTemplateNavigation__UniversalHook;
use MediaWiki\MediaWikiServices;
use MediaWiki\SpecialPage\SpecialPage;
use SkinTemplate;

class AddReaders implements SkinTemplateNavigation__UniversalHook {

	/**
	 * @param SkinTemplate $sktemplate
	 * @return bool
	 */
	protected function skipProcessing( SkinTemplate $sktemplate ) {
		if ( !$sktemplate->getTitle() || !$sktemplate->getTitle()->exists() ) {
			return true;
		}
		if ( !MediaWikiServices::getInstance()->getPermissionManager()
			->userCan(
				'viewreaders',
				$sktemplate->getUser(),
				$sktemplate->getTitle()
			)
		) {
			return true;
		}
		$config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'bsg' );
		$excludeNS = $config->get( 'ReadersNamespaceBlacklist' );
		if ( in_array( $sktemplate->getTitle()->getNamespace(), $excludeNS ) ) {
			return true;
		}
		return false;
	}

	/**
	 * // phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName
	 * @inheritDoc
	 */
	public function onSkinTemplateNavigation__Universal( $sktemplate, &$links ): void {
		if ( $this->skipProcessing( $sktemplate ) ) {
			return;
		}

		$special = SpecialPage::getTitleFor( 'Readers' );

		// Add menu entry
		$links['actions']['readers'] = [
			'class' => false,
			'text' => $sktemplate->msg( 'bs-readers-contentactions-label' ),
			'href' => $special->getLocalURL( "page={$sktemplate->getTitle()->getPrefixedText()}" ),
			'id' => 'ca-readers',
			'bs-group' => 'hidden'
		];
	}
}
