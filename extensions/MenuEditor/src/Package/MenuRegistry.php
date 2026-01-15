<?php

namespace MediaWiki\Extension\MenuEditor\Package;

use MediaWiki\Extension\MenuEditor\MenuAttributeRegistry;
use MediaWiki\Extension\MenuEditor\MenuFactory;
use MediaWiki\MediaWikiServices;

class MenuRegistry {

	/**
	 * @return array
	 */
	public static function getMenus() {
		/** @var MenuFactory $menuFactory */
		$menuFactory = MediaWikiServices::getInstance()->getService( 'MenuEditorMenuFactory' );
		$res = [];
		foreach ( $menuFactory->getAllMenus() as $key => $menu ) {
			$res[$key] = [
				'classname' => $menu->getJSClassname(),
				'toolbar' => $menu->getToolbarItems(),
				'module' => $menu->getRLModule()
			];
		}

		return $res;
	}

	/**
	 * @return array
	 */
	public static function getNodes() {
		$registry = new MenuAttributeRegistry( 'MenuEditorNodes' );
		return $registry->getAllValues();
	}
}
