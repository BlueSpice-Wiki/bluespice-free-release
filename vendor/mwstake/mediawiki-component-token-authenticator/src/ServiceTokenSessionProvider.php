<?php

namespace MWStake\MediaWiki\Component\TokenAuthenticator;

use MediaWiki\Api\Hook\ApiCheckCanExecuteHook;
use MediaWiki\Context\RequestContext;
use MediaWiki\MediaWikiServices;
use MediaWiki\Request\WebRequest;
use MediaWiki\Session\ImmutableSessionProviderWithCookie;
use MediaWiki\Session\SessionInfo;
use MediaWiki\Session\UserInfo;
use MediaWiki\User\User;
use MediaWiki\User\UserFactory;
use MediaWiki\User\UserGroupManager;
use MediaWiki\WikiMap\WikiMap;

/**
 * Provides sessions for service users authenticated via static ChatService token
 */
class ServiceTokenSessionProvider extends ImmutableSessionProviderWithCookie
implements ApiCheckCanExecuteHook {

	/** @var string */
	private string $serviceUserName;
	/** @var string */
	private string $token;
	/** @var string[] */
	private array $allowedActionApis;
	/** @var string[] */
	private array $allowedRestPaths;
	/** @var string|null */
	private ?string $accessType = null;

	/**
	 * @param UserFactory $userFactory
	 * @param CIDRValidator $CIDRValidator
	 * @param AppTokenAuthenticator $appTokenAuthenticator
	 * @param UserGroupManager $groupManager
	 * @param array $params
	 */
	public function __construct(
		private readonly UserFactory $userFactory,
		private readonly CIDRValidator $CIDRValidator,
		private readonly AppTokenAuthenticator $appTokenAuthenticator,
		private readonly UserGroupManager $groupManager,
		array $params = []
	) {
		parent::__construct();
		$this->serviceUserName = $params['service-user'];
		$this->token = $params['token'];
		$this->allowedActionApis = $params['allow-action'];
		$this->allowedRestPaths = $params['allow-rest'];
	}

	/**
	 * @return void
	 */
	protected function postInitSetup() {
		$hookContainer = MediaWikiServices::getInstance()->getHookContainer();

		$hookContainer->register( 'ApiCheckCanExecute', $this );
	}

	/**
	 * @param WebRequest $request
	 * @return SessionInfo|null
	 * @throws MWException
	 */
	public function provideSessionInfo( WebRequest $request ) {
		if ( !defined( 'MW_API' ) && !defined( 'MW_REST_API' ) ) {
			// Abstain from providing non-api sessions
			return null;
		}
		$clientIP = RequestContext::getMain()->getRequest()->getIP();
		if ( !$this->CIDRValidator->validateIP( $clientIP ) ) {
			 return null;
		}
		$authHeaders = $request->getHeader( 'Authorization' );
		if ( !$authHeaders ) {
			return null;
		}
		$authHeaders = is_array( $authHeaders ) ? $authHeaders : [ $authHeaders ];
		$allowed = false;
		foreach ( $authHeaders as $authHeader ) {
			$authType = $this->extractAuthType( $authHeader );
			if ( $authType === 'ApiKey' && $this->token && $authHeader === 'ApiKey ' . $this->token ) {
				$allowed = true;
				$this->accessType = 'limited';
			} elseif ( $authType === 'AppToken' || $authType === 'Bearer' ) {
				$token = $this->stripTokenType( $authHeader );
				$verification = $this->appTokenAuthenticator->doVerifyToken( $token );
				if ( $verification && $verification['wiki'] === WikiMap::getCurrentWikiId() ) {
					$allowed = true;
					$this->accessType = 'full';
				}
			}
		}

		if ( !$allowed ) {
			return null;
		}
		if ( defined( 'MW_REST_API' ) ) {
			if ( $this->accessType !== 'full' ) {
				$path = $request->getRequestURL();
				$restPath = wfScript( 'rest' );
				// Remove /scriptPath/rest.php from the path
				$path = substr( $path, strlen( $restPath ) );
				if ( !$this->isAllowedRestPath( $path ) ) {
					return null;
				}
			}
		}

		$user = $this->initUser();
		if ( !$user ) {
			return null;
		}

		if ( $this->sessionCookieName === null ) {
			$id = $this->hashToSessionId( implode( "\n", [
				$user->getId(),
				'service-token',
				$clientIP,
				WikiMap::getCurrentWikiId(),
			] ) );
			$persisted = false;
			$forceUse = true;
		} else {
			$id = $this->getSessionIdFromCookie( $request );
			$persisted = $id !== null;
			$forceUse = false;
		}

		return new SessionInfo( SessionInfo::MAX_PRIORITY, [
		   'provider' => $this,
		   'id' => $id,
		   'userInfo' => UserInfo::newFromUser( $user, true ),
		   'persisted' => $persisted,
		   'forceUse' => $forceUse,
		   'metadata' => [
			   'clientIP' => $clientIP,
			   'accessType' => $this->accessType
		   ],
		] );
	}

	/**
	 * @return true
	 */
	public function safeAgainstCsrf() {
		return true;
	}

	/**
	 * @return User|null
	 */
	private function initUser(): ?User {
		$user = $this->userFactory->newFromName( $this->serviceUserName );
		if ( !$user ) {
			return null;
		}
		$isSystem = $user->isSystemUser() || ( $user->getToken() !== $user->getToken() );
		if ( $isSystem ) {
			return null;
		}
		if ( !$user->isRegistered() ) {
			$user->addToDatabase();
		}
		if ( $this->accessType === 'full' ) {
			// This is not great, need to be careful
			$this->groupManager->addUserToGroup( $user, 'sysop' );
		} else {
			$this->groupManager->addUserToGroup( $user, 'bot' );
		}

		return $user;
	}

	/**
	 * @param User $user
	 * @return bool
	 */
	private function isAuthOverThisProvider( User $user ): bool {
		$session = $user->getRequest()->getSession();
		if ( $session->getProvider() === $this && $user->equals( $session->getUser() )
		) {
			return true;
		}
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function onApiCheckCanExecute( $module, $user, &$message ) {
		if ( !$this->isAuthOverThisProvider( $user ) || $this->accessType === 'full' ) {
			return true;
		}

		foreach ( $this->allowedActionApis as $allowed ) {
			if ( $module instanceof $allowed ) {
				return true;
			}
		}
		$message = 'apierror-service-token-not-allowed';
		return false;
	}

	/**
	 * @param string $path
	 * @return bool
	 */
	private function isAllowedRestPath( string $path ): bool {
		foreach ( $this->allowedRestPaths as $allowed ) {
			if ( str_starts_with( $path, $allowed ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @param string $authHeader
	 * @return string|null
	 */
	private function extractAuthType( string $authHeader ): ?string {
		if ( str_starts_with( $authHeader, 'AppToken' ) ) {
			return 'AppToken';
		}
		if ( str_starts_with( $authHeader, 'Bearer' ) ) {
			return 'Bearer';
		}
		if ( str_starts_with( $authHeader, 'ApiKey' ) ) {
			return 'ApiKey';
		}
		return null;
	}

	/**
	 * @param string $authHeader
	 * @return string
	 */
	private function stripTokenType( string $authHeader ): string {
		$parts = explode( ' ', $authHeader, 2 );
		return $parts[1] ?? '';
	}

}
