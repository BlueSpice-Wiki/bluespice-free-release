<?php

namespace BlueSpice\UserSidebar\HookHandler;

use BlueSpice\UserSidebar\Component\EditPersonalMenu;
use BlueSpice\UserSidebar\Component\SimpleCard\PersonalMenu;
use MediaWiki\Context\RequestContext;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\CommonUserInterface\Hook\MWStakeCommonUIRegisterSkinSlotComponents;

class CommonUserInterface implements MWStakeCommonUIRegisterSkinSlotComponents {

	/**
	 *
	 * @var array
	 */
	protected $sidebarItems = null;

	/**
	 * @inheritDoc
	 */
	public function onMWStakeCommonUIRegisterSkinSlotComponents( $registry ): void {
		$user = RequestContext::getMain()->getUser();
		if ( !$user ) {
			return;
		}

		$userSidebarTitle = $this->getServices()->getTitleFactory()->makeTitle(
			NS_USER,
			"{$user->getName()}/Sidebar"
		);
		if ( !$userSidebarTitle ) {
			return;
		}
		$parser = $this->getServices()->getService( 'BSUserSidebarParser' );
		$items = $parser->getSidebarItems( $userSidebarTitle, RequestContext::getMain() );
		$count = 0;
		foreach ( $items as $section => $links ) {
			$registry->register(
				'UserMenuCards',
				[
					"z-umc-bluespice-item-" . $count++ => [
						'factory' => static function ()
							use ( $section, $links ) {
								return new PersonalMenu(
									$section,
									$links
								);
						}
					]
				]
			);
		}
		$registry->register(
			'UserMenuCards',
			[
				"a-umc-edit-link" => [
					'factory' => static function ()
						use ( $userSidebarTitle ) {
						return new EditPersonalMenu( $userSidebarTitle );
					}
				]
			]
		);
	}

	/**
	 *
	 * @return MediaWikiServices
	 */
	private function getServices() {
		return MediaWikiServices::getInstance();
	}
}
