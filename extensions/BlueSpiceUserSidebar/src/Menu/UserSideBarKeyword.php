<?php

namespace BlueSpice\UserSidebar\Menu;

use MediaWiki\Extension\MenuEditor\Node\Keyword;

class UserSideBarKeyword extends Keyword {

	/**
	 * @inheritDoc
	 */
	public function getType(): string {
		return 'menu-user-sidebar-keyword';
	}
}
