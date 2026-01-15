<?php

namespace BlueSpice\InsertCategory\HookHandler;

use MediaWiki\Content\WikitextContent;
use MediaWiki\Hook\SkinTemplateNavigation__UniversalHook;
use MediaWiki\Page\WikiPageFactory;
use MediaWiki\Permissions\PermissionManager;
use SkinTemplate;

class AddInsertCategoryAction implements SkinTemplateNavigation__UniversalHook {

	/** @var PermissionManager */
	private $permissionManager;

	/** @var WikiPageFactory */
	private $wikiPageFactory;

	/**
	 *
	 * @param PermissionManager $permissionManager
	 * @param WikiPageFactory $wikiPageFactory
	 */
	public function __construct( PermissionManager $permissionManager, WikiPageFactory $wikiPageFactory ) {
		$this->permissionManager = $permissionManager;
		$this->wikiPageFactory = $wikiPageFactory;
	}

	/**
	 * @param SkinTemplate $sktemplate
	 * @return bool
	 */
	protected function skipProcessing( SkinTemplate $sktemplate ) {
		if ( $sktemplate->getRequest()->getVal( 'action', 'view' ) != 'view' ) {
			return true;
		}
		$title = $sktemplate->getTitle();
		if ( !$this->permissionManager->userCan(
				'edit',
				$sktemplate->getUser(),
				$title
			)
		) {
			return true;
		}
		$wikipage = $this->wikiPageFactory->newFromTitle( $title );
		$content = $wikipage->getContent();
		if ( !$content instanceof WikitextContent ) {
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

		$links['actions']['insert_category'] = [
			'text' => $sktemplate->msg( 'bs-insertcategory-insertcat' )->text(),
			'href' => '#',
			'class' => false,
			'id' => 'ca-insertcategory',
			'bs-group' => 'hidden'
		];
	}
}
