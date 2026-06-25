<?php

namespace BlueSpice\SaferEdit\AlertProvider;

use BlueSpice\AlertProviderBase;
use BlueSpice\IAlertProvider;
use BlueSpice\SaferEdit\EditWarningBuilder;
use MediaWiki\Context\RequestContext;
use MediaWiki\Title\Title;

class EditWarning extends AlertProviderBase {

	/**
	 * @inheritDoc
	 */
	public function getHTML() {
		$title = $this->skin->getTitle();
		if ( !$this->shouldShow( $title ) ) {
			return '';
		}

		$editWarningBuilder = new EditWarningBuilder(
			$this->loadBalancer,
			$this->getConfig(),
			$this->getUser(),
			$title
		);

		return $editWarningBuilder->getMessage();
	}

	/**
	 * @param Title|null $title
	 * @return bool
	 */
	private function shouldShow( $title ) {
		if ( !$title ) {
			return false;
		}

		$authority = RequestContext::getMain()->getAuthority();
		return $authority->probablyCan( 'edit', $title );
	}

	/**
	 * @inheritDoc
	 */
	public function getType() {
		return IAlertProvider::TYPE_WARNING;
	}

}
