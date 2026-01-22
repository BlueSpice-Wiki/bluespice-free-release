<?php

namespace MWStake\MediaWiki\Component\TokenAuthenticator\Maintenance;

class CreateServiceUser extends \LoggedUpdateMaintenance {

	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Creates a service user for use with the TokenAuthenticator.' );
	}

	/**
	 * @return void
	 */
	protected function doDBUpdates() {
		$username = 'ChatBot service user';
		$userFactory = $this->getServiceContainer()->getUserFactory();
		$user = $userFactory->newFromName( $username );
		if ( !$user->isRegistered() ) {
			$user->addToDatabase();
		}
		$this->getServiceContainer()->getUserGroupManager()
			->addUserToMultipleGroups( $user, [ 'bot', 'sysop' ] );
		$this->output( 'Set up service user: ' . $username . "\n" );
	}

	/**
	 * @return string
	 */
	protected function getUpdateKey() {
		return 'mws-component-token-authenticator-create-service-user';
	}
}
