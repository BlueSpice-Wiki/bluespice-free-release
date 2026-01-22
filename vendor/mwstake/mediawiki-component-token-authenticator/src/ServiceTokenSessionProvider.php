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
use MediaWiki\WikiMap\WikiMap;
use Wikimedia\IPUtils;

/**
 * Provides sessions for service users authenticated via static ChatService token
 */
class ServiceTokenSessionProvider extends ImmutableSessionProviderWithCookie
implements ApiCheckCanExecuteHook {

	/** @var string */
	private string $serviceUserName;
	/** @var string */
	private string $token;
	/** @var string|null */
	private ?string $cidr;
	/** @var string[] */
	private array $allowedActionApis;
	/** @var string[] */
	private array $allowedRestPaths;

	/**
	 * @param UserFactory $userFactory
	 * @param array $params
	 */
	public function __construct(
		private readonly UserFactory $userFactory,
		array $params = []
	) {
		parent::__construct();
		$this->serviceUserName = $params['service-user'];
		$this->token = $params['token'];
		$this->allowedActionApis = $params['allow-action'];
		$this->allowedRestPaths = $params['allow-rest'];

		if ( $params['cidr'] && !IPUtils::isValidRange( $params['cidr'] ) ) {
			throw new \InvalidArgumentException( 'Invalid CIDR range provided' );
		}
		$this->cidr = $params['cidr'];
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
		if ( defined( 'MW_REST_API' ) ) {
			$path = $request->getRequestURL();
			$restPath = wfScript( 'rest' );
			// Remove /scriptPath/rest.php from the path
			$path = substr( $path, strlen( $restPath ) );
			if ( !$this->isAllowedRestPath( $path ) ) {
				return null;
			}
		}
		$clientIP = RequestContext::getMain()->getRequest()->getIP();
		if ( $this->cidr && !IPUtils::isInRange( $clientIP, $this->cidr ) ) {
			return null;
		}
		$authHeader = $request->getHeader( 'Authorization' );
		if ( !$this->token || $authHeader !== 'ApiKey ' . $this->token ) {
			return null;
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
				'clientIP' => $clientIP
			],
		] );
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
		if ( !$this->isAuthOverThisProvider( $user ) ) {
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
}
