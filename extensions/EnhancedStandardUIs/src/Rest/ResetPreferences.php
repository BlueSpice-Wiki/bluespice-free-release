<?php

namespace MediaWiki\Extension\EnhancedStandardUIs\Rest;

use MediaWiki\Context\RequestContext;
use MediaWiki\Message\Message;
use MediaWiki\Rest\SimpleHandler;
use MediaWiki\User\Options\UserOptionsManager;

class ResetPreferences extends SimpleHandler {

	private UserOptionsManager $userOptionsManager;

	/**
	 * @param UserOptionsManager $userOptionsManager
	 */
	public function __construct( UserOptionsManager $userOptionsManager ) {
		$this->userOptionsManager = $userOptionsManager;
	}

	/**
	 * @inheritDoc
	 */
	public function run() {
		$context = RequestContext::getMain();
		$user = $context->getUser();

		if ( $user->isAnon() ) {
			return $this->getResponseFactory()->createHttpError( 404,
				[ 'error-msg' => 'Preferences not for anon user' ]
			);
		}

		if ( !$user->isAllowed( 'editmyoptions' ) ) {
			return $this->getResponseFactory()->createHttpError( 404,
				[ 'error-msg' => Message::newFromKey( 'enhanced-standard-uis-prefs-reset-not-allowed-label' )->text() ]
			);
		}

		$userForUpdate = $user->getInstanceForUpdate();
		$this->userOptionsManager->resetAllOptions( $userForUpdate );
		$userForUpdate->saveSettings();
		return $this->getResponseFactory()->createJson( [ 'success' => true ] );
	}

}
