<?php

namespace MediaWiki\Extension\NotifyMe\MediaWiki\Special;

use MediaWiki\Html\Html;
use MediaWiki\SpecialPage\SpecialPage;

class NotificationCenter extends SpecialPage {
	public function __construct() {
		parent::__construct( 'NotificationCenter', 'notifications-view' );
	}

	/**
	 * @param string $subPage
	 *
	 * @return void
	 */
	public function execute( $subPage ) {
		$this->setHeaders();

		// Check if user is logged in, there is no sense to show "Notification Center"
		// for anonymous user
		$this->requireLogin();

		$output = $this->getOutput();

		$output->enableOOUI();
		$output->addModules( 'ext.notifyme.specialPage' );

		$output->addJsConfigVars( 'wgNotificationsSpecialPageLinks', [
			'preferences' => SpecialPage::getTitleFor(
				'Preferences', false, 'mw-prefsection-notifications'
			)->getLinkURL(),
		] );

		$output->addHTML( Html::element( 'div', [
			'id' => 'notifications-overview'
		] ) );
	}
}
