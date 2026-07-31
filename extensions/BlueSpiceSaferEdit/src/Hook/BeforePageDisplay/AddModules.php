<?php

namespace BlueSpice\SaferEdit\Hook\BeforePageDisplay;

use BlueSpice\Hook\BeforePageDisplay;
use BlueSpice\SaferEdit\SaferEditManager;

class AddModules extends BeforePageDisplay {

	/**
	 * @var SaferEditManager
	 */
	protected $seManager;

	/**
	 * @return bool
	 */
	protected function doProcess() {
		$this->seManager = $this->getServices()->getService( 'BSSaferEditManager' );

		$isEditMode = $this->isEditMode();
		$shouldShowWarning = $this->shouldShowWarning();
		if ( $isEditMode || $shouldShowWarning ) {
			$this->out->addModules( 'ext.bluespice.saferedit.init' );
			$this->out->addJsConfigVars( 'bsSaferEditIsEditMode', $isEditMode );
			$this->out->addJsConfigVars( 'bsSaferEditDisplayWarning', $shouldShowWarning );
		}

		return true;
	}

	/**
	 * @return bool
	 */
	private function isEditMode() {
		$result = false;
		$this->seManager->askEnvironmentalCheckers( 'isEditMode', $result );
		return $result;
	}

	/**
	 * @return bool
	 */
	private function shouldShowWarning() {
		$context = $this->getContext();
		$title = $context->getTitle();
		if ( !$title ) {
			return false;
		}

		$result = false;
		$this->seManager->askEnvironmentalCheckers( 'shouldShowWarning', $result );

		/** Only show warning if the user can edit the page */
		$userCanEdit = $context->getAuthority()->probablyCan( 'edit', $title );
		$result = $result && $userCanEdit;

		return $result;
	}
}
