<?php

namespace MediaWiki\Extension\MenuEditor\Hook;

use Article;
use MediaWiki;
use MediaWiki\Extension\MenuEditor\EditPermissionProvider;
use MediaWiki\Extension\MenuEditor\MenuFactory;
use MediaWiki\Hook\MediaWikiPerformActionHook;
use MediaWiki\Hook\SkinTemplateNavigation__UniversalHook;
use MediaWiki\HookContainer\HookContainer;
use MediaWiki\Html\Html;
use MediaWiki\Output\OutputPage;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Request\WebRequest;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use SkinTemplate;

class EditActionHookHandler implements
	MediaWikiPerformActionHook,
	SkinTemplateNavigation__UniversalHook
{
	/** @var Title|null */
	private $title = null;

	/** @var MenuFactory */
	private $menuFactory;

	/** @var PermissionManager */
	private $permissionManager;

	/**
	 * @param HookContainer $hookContainer
	 * @param MenuFactory $menuFactory
	 * @param PermissionManager $permissionManager
	 *
	 */
	public function __construct(
		HookContainer $hookContainer,
		MenuFactory $menuFactory,
		PermissionManager $permissionManager
	) {
		$this->menuFactory = $menuFactory;
		$this->permissionManager = $permissionManager;
		$hookContainer->register( 'MediaWikiPerformAction', [ $this, 'onMediaWikiPerformAction' ] );
		$hookContainer->register(
			'SkinTemplateNavigation::Universal',
			[ $this, 'onSkinTemplateNavigation__Universal' ]
		);
	}

	/**
	 * @param OutputPage $output
	 * @param Article $article
	 * @param Title $title
	 * @param User $user
	 * @param WebRequest $request
	 * @param MediaWiki $mediaWiki
	 * @return bool|void
	 */
	public function onMediaWikiPerformAction( $output, $article, $title, $user, $request, $mediaWiki ) {
		$action = $request->getText( 'action', $request->getText( 'veaction', 'view' ) );

		if ( !in_array( $action, [ 'view', 'menueditsource', 'edit', 'create' ] ) ) {
			return true;
		}

		if ( $request->getVal( 'diff' ) !== null ) {
			return true;
		}

		if ( $action === 'edit' && $request->getVal( 'undo' ) !== null ) {
			return true;
		}

		if ( $action === 'menueditsource' ) {
			$request->setVal( 'action', 'edit' );
			return true;
		}

		$menus = $this->menuFactory->getAllMenus();
		$applied = false;

		foreach ( $menus as $key => $menu ) {
			if ( $menu->appliesToTitle( $title ) ) {
				$applied = true;
				$appliedMenu = $menu;
			}
		}
		if ( !$applied ) {
			return true;
		}

		$editRight = 'editinterface';
		if ( $appliedMenu instanceof EditPermissionProvider ) {
			$editRight = $appliedMenu->getEditRight();
		}

		$userCanEdit = $user->isAllowed( $editRight );
		if ( !$userCanEdit ) {
			return true;
		}

		$this->title = $title;
		$output->setPageTitle( $title->getPrefixedText() );
		$output->addModules( 'ext.menuEditor.pageEditOverride' );
		$output->addHTML( Html::element( 'div', [
			'id' => 'menuEditor-container',
			'data-mode' => $action,
			'data-menu-key' => $appliedMenu->getKey(),
			'data-default' => json_encode( $appliedMenu->getEmptyContent() ),
		] ) );
		if ( $action === 'edit' && $userCanEdit ) {
			$request->setVal( 'action', 'menuedit' );
		}

		return false;
	}

	/**
	 * // phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName
	 * @param SkinTemplate $sktemplate
	 * @param array &$links
	 */
	public function onSkinTemplateNavigation__Universal( $sktemplate, &$links ): void {
		if ( !$this->title ) {
			return;
		}

		$user = $sktemplate->getUser();
		$userCanEdit = $user->isAllowed( 'editinterface' );

		if ( $userCanEdit ) {
			$links['views']['menueditsource'] = [
				'text' => $sktemplate->msg( "menueditor-action-menueditsource" )->text(),
				'href' => $this->title->getLocalURL( [ 'action' => 'menueditsource' ] ),
				'class' => false,
				'id' => 'ca-menueditsource',
				'position' => 12,
			];
			$links['views']['edit'] = [
				'text' => $sktemplate->msg( "menueditor-action-menuedit" )->text(),
				'href' => $this->title->getLocalURL( [ 'action' => 'edit' ] ),
				'class' => false,
				'id' => 'ca-edit',
				'position' => 10,
			];
		}
	}
}
