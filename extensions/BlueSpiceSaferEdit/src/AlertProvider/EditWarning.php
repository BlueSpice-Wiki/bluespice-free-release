<?php

namespace BlueSpice\SaferEdit\AlertProvider;

use BlueSpice\AlertProviderBase;
use BlueSpice\IAlertProvider;
use BlueSpice\SaferEdit\EditWarningBuilder;

class EditWarning extends AlertProviderBase {

	/**
	 * @inheritDoc
	 */
	public function getHTML() {
		$currentTitle = $this->skin->getTitle();
		if ( $currentTitle === null ) {
			return '';
		}

		$editWarningBuilder = new EditWarningBuilder(
			$this->loadBalancer,
			$this->getConfig(),
			$this->getUser(),
			$currentTitle
		);

		return $editWarningBuilder->getMessage();
	}

	/**
	 * @inheritDoc
	 */
	public function getType() {
		return IAlertProvider::TYPE_WARNING;
	}

}
