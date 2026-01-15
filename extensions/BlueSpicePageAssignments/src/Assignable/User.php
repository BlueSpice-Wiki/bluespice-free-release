<?php

namespace BlueSpice\PageAssignments\Assignable;

use BlueSpice\PageAssignments\Data\Assignable\User\Store;
use MediaWiki\MediaWikiServices;

class User extends \BlueSpice\PageAssignments\Assignable {

	/**
	 *
	 * @return Store
	 */
	public function getStore() {
		$services = MediaWikiServices::getInstance();
		return new Store(
			$services->getDBLoadBalancer(),
			$services->getUserFactory(),
			$services->getLinkRenderer(),
			$services->getTitleFactory(),
			$services->getService( 'MWStakeCommonUtilsConfig' ),
			$this->context->getTitle()
		);
	}

	/**
	 *
	 * @return string
	 */
	public function getAssignmentClass() {
		return "\\BlueSpice\\PageAssignments\\Assignment\\User";
	}

	/**
	 *
	 * @return string
	 */
	public function getRendererKey() {
		return "assignment-user";
	}

	/**
	 *
	 * @return string
	 */
	public function getTypeMessageKey() {
		return "bs-pageassignments-assignee-type-user";
	}

}
