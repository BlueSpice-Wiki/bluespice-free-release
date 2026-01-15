<?php

namespace BlueSpice\UserSidebar\Hook\EditFormPreloadText;

use BlueSpice\Hook\EditFormPreloadText;
use MediaWiki\Title\Title;

class UserSidebarDefaultText extends EditFormPreloadText {

	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		$user = $this->getContext()->getUser();
		$userPage = Title::makeTitle( NS_USER, $user->getName() . "/Sidebar" );
		if ( $this->title->equals( $userPage ) ) {
			return false;
		}
		return true;
	}

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		$this->text = implode( "\n", $this->getWidgetLinks() );
		return true;
	}

	/**
	 *
	 * @return array
	 */
	protected function getWidgetLinks() {
		$factory = $this->getServices()->getService( 'BSUserSidebarWidgetFactory' );
		$widgets = [];
		foreach ( $factory->getAllKeys() as $key ) {
			$widgets[] = "* $key";
		}
		return $widgets;
	}
}
