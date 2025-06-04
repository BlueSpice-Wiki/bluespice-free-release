<?php

namespace MediaWiki\Extension\UserProfile\Hooks;

use MediaWiki\Extension\UserProfile\Hooks\Interface\UserProfileBeforeSetProfileDataHook;
use MediaWiki\Extension\UserProfile\Hooks\Interface\UserProfileGetProfileDataHook;
use MediaWiki\Language\Language;
use MediaWiki\Permissions\Authority;
use MediaWiki\User\User;
use MediaWiki\User\UserFactory;
use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\DynamicFileDispatcher\DynamicFileDispatcherFactory;

class HandleExternalProfileData implements
	UserProfileGetProfileDataHook,
	UserProfileBeforeSetProfileDataHook
{

	/** @var UserFactory */
	private $userFactory;

	/** @var Language */
	private $language;

	/** @var DynamicFileDispatcherFactory */
	private $dynamicFileDispatcherFactory;

	/**
	 * @param UserFactory $userFactory
	 * @param Language $contentLanguage
	 * @param DynamicFileDispatcherFactory $dynamicFileDispatcherFactory
	 */
	public function __construct(
		UserFactory $userFactory, Language $contentLanguage,
		DynamicFileDispatcherFactory $dynamicFileDispatcherFactory
	) {
		$this->userFactory = $userFactory;
		$this->language = $contentLanguage;
		$this->dynamicFileDispatcherFactory = $dynamicFileDispatcherFactory;
	}

	/**
	 * @param array &$data
	 * @param UserIdentity $forUser
	 * @param Authority $actor
	 * @return void
	 */
	public function onUserProfileBeforeSetProfileData(
		array &$data, UserIdentity $forUser, Authority $actor
	): void {
		$forUser = $this->userFactory->newFromUserIdentity( $forUser );
		$forUser->setRealName( $data['realName'] ?? '' );
		$forUser->setEmail( $data['email'] ?? '' );
		$forUser->saveSettings();

		// Do not duplicate data
		unset( $data['realName'], $data['email'] );
	}

	/**
	 * @param array &$data
	 * @param User $user
	 * @param Authority $requester
	 * @return void
	 */
	public function onUserProfileGetProfileData( array &$data, User $user, Authority $requester ): void {
		$data['username'] = $user->getName();
		$data['realName'] = $user->getRealName();
		$data['email'] = $user->mEmail;
		$data['imageUrl'] = $this->getImageUrl( $user );
		$data['numberOfEdits'] = $this->language->formatNum( $user->getEditCount() );
		$data['joined'] = $this->language->userDate( $user->getRegistration(), $requester->getUser() );
	}

	/**
	 * @param User $user
	 * @return string
	 */
	private function getImageUrl( User $user ): string {
		return $this->dynamicFileDispatcherFactory->getUrl( 'userprofileimage', [
			'username' => $user->getName(),
			'width' => 200,
			'height' => 200
		] );
	}
}
