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
		$services = MediaWikiServices::getInstance();
		return new Store(
			$services->getService( 'MWStakeCommonUtilsFactory' ),
			$services->getService( 'MWStakeCommonUtilsConfig' ),
			$this->context->getTitle(),
			$services->getHookContainer()
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
