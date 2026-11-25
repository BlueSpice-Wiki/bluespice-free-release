<?php

use BlueSpice\RSSFeeder\RSSTokenProvider;
use MediaWiki\Context\IContextSource;
use MediaWiki\Context\RequestContext;
use MediaWiki\MediaWikiServices;
use MediaWiki\Request\WebRequest;
use MediaWiki\User\User;

class RSSAuthenticator {
	public const TOKEN_SALT = 'rss_salt';
	/**
	 * @var WebRequest
	 */
	protected $request;

	/**
	 * @var RequestContext
	 */
	protected $context;

	/**
	 * @var RSSTokenProvider
	 */
	protected $tokenProvider;

	/**
	 * @param WebRequest $request
	 * @param IContextSource $context
	 * @param RSSTokenProvider $tokenProvider
	 */
	public function __construct( WebRequest $request, IContextSource $context, RSSTokenProvider $tokenProvider ) {
		$this->request = $request;
		$this->context = $context;
		$this->tokenProvider = $tokenProvider;
	}

	/**
	 * Returns the user identified by the username and token given in the
	 * request. Does not change the session.
	 *
	 * @return User|null
	 */
	public function getUserFromRequest(): ?User {
		$userName = $this->request->getVal( 'u', '' );
		if ( !is_string( $userName ) || $userName === '' ) {
			return null;
		}

		$requestToken = $this->request->getVal( 'h', '' );
		if ( !is_string( $requestToken ) || $requestToken === '' ) {
			return null;
		}

		$user = MediaWikiServices::getInstance()->getUserFactory()
			->newFromName( $userName );
		if ( $user instanceof User == false || $user->getId() == 0 ) {
			// User does not exist
			return null;
		}

		if ( !$this->tokenProvider->isValidToken( $requestToken, $user ) ) {
			return null;
		}

		return $user;
	}

	/**
	 * Sets the user identified by the request as the user of the context,
	 * for the current request only. It does not log in the user.
	 *
	 * @deprecated Use getUserFromRequest()
	 * @return bool
	 */
	public function logInUser() {
		$user = $this->getUserFromRequest();
		if ( !$user ) {
			return false;
		}

		$this->context->setUser( $user );
		return true;
	}
}
