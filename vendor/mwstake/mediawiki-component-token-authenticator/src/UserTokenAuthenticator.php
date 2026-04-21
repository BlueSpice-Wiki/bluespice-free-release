<?php

namespace MWStake\MediaWiki\Component\TokenAuthenticator;

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

class UserTokenAuthenticator extends TokenAuthenticator {

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
		BagOStuff $sessionCache,
		private readonly UserFactory $userFactory,
		private readonly UserGroupManager $groupManager,
		private readonly UserOptionsLookup $userOptionsLookup,
		private readonly LanguageNameUtils $languageNameUtils,
		private readonly Language $contentLanguage,
		private readonly HookContainer $hookContainer,
		string $salt = ''
	) {
		parent::__construct( $sessionCache, $salt );
	}

	/**
	 * @param UserIdentity $user
	 * @return string
	 * @throws \Random\RandomException
	 */
	public function generateToken( UserIdentity $user ) {
		$data = [
			'user' => $user->getName(),
			'registered' => $user->isRegistered(),
		];
		return parent::doGenerateToken( $data );
	}

	/**
	 * @param UserIdentity $user
	 * @return string
	 * @throws \Random\RandomException
	 */
	public function generateTokenWithIssuer( UserIdentity $user ) {
		$data = [
			'user' => $user->getName(),
			'registered' => $user->isRegistered(),
		];
		return parent::doGenerateTokenWithIssuer( $data );
	}

	/**
	 * @param string $token
	 * @return UserIdentity|null
	 */
	public function verifyToken( string $token ): ?UserIdentity {
		$data = parent::doVerifyToken( $token );
		if ( !$data ) {
			return null;
		}
		if ( $data['registered'] ) {
			return $this->userFactory->newFromName( $data['user'] );
		} else {
			return $this->userFactory->newAnonymous( $data['user'] );
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
