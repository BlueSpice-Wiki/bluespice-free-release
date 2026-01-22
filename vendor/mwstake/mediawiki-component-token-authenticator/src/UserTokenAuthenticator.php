<?php

namespace MWStake\MediaWiki\Component\TokenAuthenticator;

use InvalidArgumentException;
use MediaWiki\HookContainer\HookContainer;
use MediaWiki\Language\Language;
use MediaWiki\Languages\LanguageNameUtils;
use MediaWiki\User\Options\UserOptionsLookup;
use MediaWiki\User\UserFactory;
use MediaWiki\User\UserGroupManager;
use MediaWiki\User\UserIdentity;
use MediaWiki\Utils\UrlUtils;
use MediaWiki\WikiMap\WikiMap;
use Wikimedia\ObjectCache\BagOStuff;

class UserTokenAuthenticator {
	private const TTL = 10;

	/**
	 * @param UrlUtils $urlUtils
	 * @param BagOStuff $sessionCache
	 * @param UserFactory $userFactory
	 * @param UserGroupManager $groupManager
	 * @param UserOptionsLookup $userOptionsLookup
	 * @param LanguageNameUtils $languageNameUtils
	 * @param Language $contentLanguage
	 * @param HookContainer $hookContainer
	 * @param string $salt
	 */
	public function __construct(
		private readonly UrlUtils $urlUtils,
		private readonly BagOStuff $sessionCache,
		private readonly UserFactory $userFactory,
		private readonly UserGroupManager $groupManager,
		private readonly UserOptionsLookup $userOptionsLookup,
		private readonly LanguageNameUtils $languageNameUtils,
		private readonly Language $contentLanguage,
		private readonly HookContainer $hookContainer,
		private readonly string $salt = ''
	) {
	}

	/**
	 * @param UserIdentity $user
	 * @return string
	 * @throws \Random\RandomException
	 */
	public function generateToken( UserIdentity $user ) {
		$username = $user->getName();
		$token = bin2hex( random_bytes( 16 ) );
		if ( $this->sessionCache->set(
			$this->sessionCache->makeKey( $token ),
			[ 'user' => $username, 'registered' => $user->isRegistered(), 'wiki' => WikiMap::getCurrentWikiId() ],
			static::TTL
		) ) {
			return $token;
		} else {
			throw new InvalidArgumentException( 'Failed to store user token in cache.' );
		}
	}

	/**
	 * Generates a token and bakes in the issues, to be used for verification.
	 * Salt must be set for this method to work
	 *
	 * @param UserIdentity $user
	 * @return string
	 * @throws \Random\RandomException
	 */
	public function generateTokenWithIssuer( UserIdentity $user ) {
		if ( !$this->salt ) {
			throw new InvalidArgumentException( 'Salt must be set to generate a token with issuer.' );
		}
		$token = $this->generateToken( $user );
		$callbackUrl = wfScript( 'rest' );
		$signature = hash_hmac( 'sha256', "$callbackUrl$token", $this->salt );
		return base64_encode( json_encode( [
			'verifyCallback' => $callbackUrl,
			'token' => $token,
			'sig' => $signature,
		] ) );
	}

	/**
	 * @param string $token
	 * @return UserIdentity|null
	 */
	public function verifyToken( string $token ): ?UserIdentity {
		$key = $this->sessionCache->makeKey( $token );
		$value = $this->sessionCache->get( $key );
		if ( !$value ) {
			return null;
		}
		if ( $value['registered'] ) {
			return $this->userFactory->newFromName( $value['user'] );
		} else {
			return $this->userFactory->newAnonymous( $value['user'] );
		}
	}

	/**
	 * @param UserIdentity $user
	 * @return AuthInfo|null
	 */
	public function getAuthInfo( UserIdentity $user ): ?AuthInfo {
		$meta = [];
		if ( $user->isAnon() ) {
			$meta['anon'] = true;
		}
		$this->hookContainer->run( 'MWStakeTokenAuthenticatorGetAuthInfo', [ $user, &$meta ] );
		return new AuthInfo(
			$user,
			WikiMap::getCurrentWikiId(),
			$this->getUserLanguage( $user ),
			$this->groupManager->getUserEffectiveGroups( $user ),
			$meta
		);
	}

	/**
	 * @param UserIdentity $user
	 * @return string
	 */
	private function getUserLanguage( UserIdentity $user ): string {
		$option = $this->userOptionsLookup->getOption( $user, 'language', '' );
		if ( $option && $this->languageNameUtils->isValidCode( $option ) ) {
			return $option;
		}
		return $this->contentLanguage->getCode() ?? 'en';
	}
}
