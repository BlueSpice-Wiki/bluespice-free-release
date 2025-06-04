<?php

namespace BlueSpice\Avatars;

use MediaWiki\Config\Config;
use MediaWiki\MediaWikiServices;
use MediaWiki\User\User;

class Generator {
	public const FILE_PREFIX = "BS_avatar_";

	public const PARAM_OVERWRITE = 'overwrite';
	public const PARAM_HEIGHT = 'height';
	public const PARAM_WIDTH = 'width';

	/**
	 *
	 * @var Config
	 */
	protected $config = null;

	/**
	 *
	 * @var AvatarGeneratorFactory
	 */
	protected $factory = null;

	/**
	 *
	 * @param Config $config
	 * @param AvatarGeneratorFactory|null $factory
	 */
	public function __construct( Config $config, ?AvatarGeneratorFactory $factory = null ) {
		$this->config = $config;
		if ( !$factory ) {
			// deprecated since version 3.1.13 - Use Service BSAvatarsAvatarGenerator
			// to create this instance
			wfDebugLog( 'bluespice-deprecations', __METHOD__, 'private' );
			$factory = MediaWikiServices::getInstance()->getService(
				'BSAvatarsAvatarGeneratorFactory'
			);
		}
		$this->factory = $factory;
	}

	/**
	 *
	 * @param User $user
	 * @param array $params
	 * @return string
	 */
	public function generate( User $user, array $params = [] ) {
		$defaultSize = 1024;

		$oFile = $this->getAvatarFile( $user );
		if ( !$oFile ) {
			return '';
		}

		if ( !$oFile->exists() || isset( $params[static::PARAM_OVERWRITE] ) ) {
			$generator = $this->factory->newFromName(
				$this->config->get( 'AvatarsGenerator' )
			);
			if ( !$generator ) {
				throw new \MWException(
					"Avatar generator '{$this->config->get( 'AvatarsGenerator' )}' not found!"
				);
			}

			$rawPNGAvatar = $generator->generate( $user, $defaultSize );

			$status = \BsFileSystemHelper::saveToDataDirectory(
				$oFile->getName(),
				$rawPNGAvatar,
				'Avatars'
			);
			if ( !$status->isGood() ) {
				throw new \MWException(
					'FATAL: Avatar could not be saved! ' . $status->getMessage()
				);
			}
			# Delete thumb folder if it exists
			$status = \BsFileSystemHelper::deleteFolder(
				"Avatars/thumb/{$oFile->getName()}",
				true
			);
			if ( !$status->isGood() ) {
				throw new \MWException(
					'FATAL: Avatar thumbs could no be deleted!'
				);
			}
			$oFile = \BsFileSystemHelper::getFileFromRepoName(
				$oFile->getName(),
				'Avatars'
			);

			$user->invalidateCache();
		}
	}

	/**
	 * Gets Avatar file from user ID
	 * @param User $user
	 * @return bool|\File
	 */
	public function getAvatarFile( User $user ) {
		return \BsFileSystemHelper::getFileFromRepoName(
			static::FILE_PREFIX . $user->getId() . ".png",
			'Avatars'
		);
	}
}
