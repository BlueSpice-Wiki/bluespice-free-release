<?php

namespace BlueSpice\SaferEdit\AlertProvider;

use BlueSpice\AlertProviderBase;
use BlueSpice\IAlertProvider;
use BlueSpice\SaferEdit\EditWarningBuilder;
use MediaWiki\Context\RequestContext;

class EditWarning extends AlertProviderBase {

	/**
	 * @inheritDoc
	 */
	public function getHTML() {
		$currentTitle = $this->skin->getTitle();
		if ( $currentTitle === null ) {
			return '';
		}

		/**
		 * Fix for collab banner also shown for unauthorized users
		 */
		$authority = RequestContext::getMain()->getAuthority();
		$userCanEdit = $authority->probablyCan( 'edit', $currentTitle );

		if ( $userCanEdit ) {
			$editWarningBuilder = new EditWarningBuilder(
				$this->loadBalancer,
				$this->getConfig(),
				$this->getUser(),
				$currentTitle
			);

			return $editWarningBuilder->getMessage();
		}
		return '';
	}

	/**
	 * @inheritDoc
	 */
	public function getType() {
		return IAlertProvider::TYPE_WARNING;
	}

}
