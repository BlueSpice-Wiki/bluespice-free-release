<?php

namespace MediaWiki\Extension\UserProfile\DynamicFileDispatcher;

use MediaWiki\Extension\UserProfile\ProfileImage\ProfileImageInfo;
use MediaWiki\Extension\UserProfile\ProfileImage\ProfileImageProviderFactory;
use MediaWiki\Permissions\Authority;
use MediaWiki\User\User;
use MediaWiki\User\UserFactory;
use MWStake\MediaWiki\Component\DynamicFileDispatcher\IDynamicFile;
use MWStake\MediaWiki\Component\DynamicFileDispatcher\Module\UserProfileImage as DefaultImageModule;

class UserProfileImage extends DefaultImageModule {

	/** @var User|null */
	private $user = null;

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
	 * @param Authority $user
	 * @param array $params
	 * @return bool
	 */
	public function isAuthorized( Authority $user, array $params ): bool {
		$this->user = $user->getUser();
		return parent::isAuthorized( $user, $params );
	}

	/**
	 * @param array $params
	 * @return IDynamicFile|null
	 */
	public function getFile( array $params ): ?IDynamicFile {
		if ( $params['username'] ) {
			$this->user = $this->userFactory->newFromName( $params['username'] );
		}
		if ( !$this->user || !$this->user->isRegistered() ) {
			return parent::getFile( $params );
		}
		return $this->getImageFromProviders( $params );
	}

	/**
	 *
	 * @param array $params
	 * @return IDynamicFile|null
	 */
	protected function getImageFromProviders( array $params ): ?IDynamicFile {
		foreach ( $this->profileImageProviderFactory->getAll() as $provider ) {
			$info = $provider->provide( $this->user, $params );
			if ( $info instanceof ProfileImageInfo ) {
				return new DirectPathDynamicFile( $info->getPath(), $info->getMimeType() );
			}
		}
		return parent::getFile( $params );
	}
}
