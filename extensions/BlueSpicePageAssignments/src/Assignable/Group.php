<?php

namespace BlueSpice\PageAssignments\Assignable;

use BlueSpice\PageAssignments\Data\Assignable\Group\Store;
use MediaWiki\MediaWikiServices;

class Group extends \BlueSpice\PageAssignments\Assignable {

	/**
	 *
	 * @return Store
	 */
	public function getStore() {
		return new Store(
			MediaWikiServices::getInstance()->getService( 'MWStakeCommonUtilsFactory' ),
			MediaWikiServices::getInstance()->getService( 'MWStakeCommonUtilsConfig' ),
			$this->context->getTitle()
		);
	}

	/**
	 *
	 * @return string
	 */
	public function getAssignmentClass() {
		return "\\BlueSpice\\PageAssignments\\Assignment\\Group";
	}

	/**
	 *
	 * @return string
	 */
	public function getTypeMessageKey() {
		return "bs-pageassignments-assignee-type-group";
	}
}
