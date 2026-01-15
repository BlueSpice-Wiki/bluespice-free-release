<?php

namespace MediaWiki\Extension\UserProfile\Hooks;

use MediaWiki\Extension\UserProfile\DynamicFileDispatcher\UserProfileImage;
use MediaWiki\Extension\UserProfile\ProfileImage\ProfileImageProviderFactory;
use MediaWiki\User\UserFactory;
use MWStake\MediaWiki\Component\DynamicFileDispatcher\MWStakeDynamicFileDispatcherRegisterModuleHook;

class RegisterDynamicFileModule implements MWStakeDynamicFileDispatcherRegisterModuleHook {

	/** @var UserFactory */
	private $userFactory;

	/** @var ProfileImageProviderFactory */
	private $profileImageProviderFactory;

	/**
	 * @param UserFactory $userFactory
	 * @param ProfileImageProviderFactory $profileImageProviderFactory
	 */
	public function __construct( UserFactory $userFactory, ProfileImageProviderFactory $profileImageProviderFactory ) {
		$this->userFactory = $userFactory;
		$this->profileImageProviderFactory = $profileImageProviderFactory;
	}

	/**
	 * @inheritDoc
	 */
	public function onMWStakeDynamicFileDispatcherRegisterModule( &$modules ) {
		$modules['userprofileimage'] = new UserProfileImage( $this->userFactory, $this->profileImageProviderFactory );
	}
}
