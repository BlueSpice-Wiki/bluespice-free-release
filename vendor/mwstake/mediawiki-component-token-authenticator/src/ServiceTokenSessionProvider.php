<?php

namespace MWStake\MediaWiki\Component\TokenAuthenticator;

use InvalidArgumentException;
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
	/** @var array */
	private array $tokens;
	/** @var string */
	private string $mainCIDR;
	/** @var string */
	private string $tokenCIDR;
	/** @var string[] */
	private array $allowedActionApis = [];
	/** @var string[] */
	private array $allowedRestPaths = [];
	/** @var string|null */
	private ?string $accessType = null;

	/**
	 * @param UserFactory $userFactory
	 * @param AppTokenAuthenticator $appTokenAuthenticator
	 * @param UserGroupManager $groupManager
	 * @param array $params
	 */
	public function __construct(
		private readonly UserFactory $userFactory,
		private readonly AppTokenAuthenticator $appTokenAuthenticator,
		private readonly UserGroupManager $groupManager,
		array $params = []
	) {
		parent::__construct();
		$this->serviceUserName = $params['service-user'];
		$this->tokens = $params['tokens'];
		$mainCIDR = $params['main-cidr'] ?? '';
		if ( !CIDRValidator::isValidCIDR( $mainCIDR ) ) {
			throw new InvalidArgumentException(
				'ServiceTokenSessionProvider: "main-cidr" must be a valid, non-empty CIDR range'
			);
		}
		$this->mainCIDR = $mainCIDR;
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
		$this->allowedRestPaths = [];
		$this->allowedActionApis = [];
		$this->accessType = null;
		$this->tokenCIDR = $this->mainCIDR;

		if ( !defined( 'MW_API' ) && !defined( 'MW_REST_API' ) ) {
			// Abstain from providing non-api sessions
			return null;
		}
		$clientIP = RequestContext::getMain()->getRequest()->getIP();
		$authHeaders = $request->getHeader( 'Authorization' );
		if ( !$authHeaders ) {
			$this->logger->debug( 'ServiceTokenSessionProvider: No Authorization header present - bailing out' );
			return null;
		}
		$cidrValidator = new CIDRValidator();

		$authHeaders = is_array( $authHeaders ) ? $authHeaders : [ $authHeaders ];
		$allowed = false;
		foreach ( $authHeaders as $authHeader ) {
			$authType = $this->extractAuthType( $authHeader );
			if ( $authType === 'ApiKey' ) {
				if ( $this->matchToken( $authHeader ) ) {
					if ( !$cidrValidator->validateIP( $clientIP, $this->tokenCIDR ) ) {
						$this->logger->info(
							'ServiceTokenSessionProvider: Rejecting request from IP {clientIP} - ' .
							'not in allowed CIDR range: {cidr}',
							[ 'clientIP' => $clientIP, 'cidr' => $this->tokenCIDR ]
						);
						return null;
					}
					$this->logger->info(
						'ServiceTokenSessionProvider: Valid ApiKey token provided - allowing access to configured APIs'
					);
					$allowed = true;
					$this->accessType = 'limited';
				}
			} elseif ( $authType === 'AppToken' || $authType === 'Bearer' ) {
				if ( !$cidrValidator->validateIP( $clientIP, $this->mainCIDR ) ) {
					$this->logger->info(
						'ServiceTokenSessionProvider: Rejecting request from IP {clientIP} - ' .
						'not in allowed CIDR range: {cidr}',
						[ 'clientIP' => $clientIP, 'cidr' => $this->mainCIDR ]
					);
					return null;
				}
				$token = $this->stripTokenType( $authHeader );
				$verification = $this->appTokenAuthenticator->doVerifyToken( $token );
				if ( $verification && $verification['wiki'] === WikiMap::getCurrentWikiId() ) {
					$this->logger->info(
						'ServiceTokenSessionProvider: Valid AppToken provided - allowing full access',
						[ 'wiki' => $verification['wiki'] ]
					);
					$allowed = true;
					$this->accessType = 'full';
				}
			}
		}

		if ( !$allowed ) {
			$this->logger->info(
				'ServiceTokenSessionProvider: No valid token provided in Authorization header - bailing out',
				[ 'authHeaders' => $authHeaders ]
			);
			return null;
		}
		if ( defined( 'MW_REST_API' ) ) {
			if ( $this->accessType !== 'full' ) {
				$path = $request->getRequestURL();
				$restPath = wfScript( 'rest' );
				// Remove /scriptPath/rest.php from the path
				$path = substr( $path, strlen( $restPath ) );
				if ( !$this->isAllowedRestPath( $path ) ) {
					$this->logger->info(
						'ServiceTokenSessionProvider: Access to REST path {path} is denied for limited token',
						[ 'path' => $path ]
					);
					return null;
				}
			}
		}

		$user = $this->initUser();
		if ( !$user ) {
			$this->logger->error( 'ServiceTokenSessionProvider: Failed to initialize user for service token' );
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
		$this->logger->info(
			'ServiceTokenSessionProvider: API module {module} is not allowed for limited token',
			[ 'module' => get_class( $module ) ]
		);
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

	/**
	 * @param string $authHeader
	 * @return bool
	 */
	private function matchToken( string $authHeader ): bool {
		foreach ( $this->tokens as $tokenData ) {
			if ( !isset( $tokenData['token'] ) ) {
				continue;
			}
			if ( $authHeader === 'ApiKey ' . $tokenData['token'] ) {
				if ( !empty( $tokenData['context-user'] ) ) {
					$this->serviceUserName = $tokenData['context-user'];
				}
				$this->allowedActionApis = $tokenData['api-modules'] ?? [];
				$this->allowedRestPaths = $tokenData['rest-paths'] ?? [];
				if ( isset( $tokenData['cidr'] ) ) {
					if ( CIDRValidator::isValidCIDR( $tokenData['cidr'] ) ) {
						$this->tokenCIDR = $tokenData['cidr'];
					} else {
						// CIDR restriction must never be disabled - fall back to the global range
						$this->logger->warning(
							'ServiceTokenSessionProvider: Token defines an empty or invalid CIDR range - ' .
							'falling back to $mwsgTokenAuthenticatorServiceCIDR'
						);
						$this->tokenCIDR = $this->mainCIDR;
					}
				}
				return true;
			}
		}

		return false;
	}

}
