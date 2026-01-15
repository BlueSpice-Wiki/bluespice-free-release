<?php

namespace BlueSpice\PageAssignments\Hook\BeforePageDisplay;

class AddResources extends \BlueSpice\Hook\BeforePageDisplay {

	protected function doProcess() {
		if ( $this->out->getRequest()->getVal( 'action', 'view' ) !== 'view' ) {
			return true;
		}
		$title = $this->out->getTitle();
		if ( $title && $title->isSpecialPage() ) {
			return true;
		}

		$this->out->addModules( 'ext.pageassignments.scripts' );

		return true;
	}
}
