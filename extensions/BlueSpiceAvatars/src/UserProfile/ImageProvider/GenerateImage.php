<?php

namespace BlueSpice\Avatars\UserProfile\ImageProvider;

use BlueSpice\Avatars\Generator;
use File;
use MediaWiki\Extension\UserProfile\ProfileImage\IProfileImageProvider;
use MediaWiki\Extension\UserProfile\ProfileImage\ProfileImageInfo;
use MediaWiki\User\User;
use MediaWiki\User\UserIdentity;
use MWException;
use ThumbnailImage;

class GenerateImage implements IProfileImageProvider {

	/** @var Generator */
	protected $generator;

	/** @var ThumbnailImage|null */
	private $file = null;

	/**
	 * @param Generator $generator
	 */
	public function __construct( Generator $generator ) {
		$this->generator = $generator;
	}

	/**
	 * @inheritDoc
	 */
	public function provide( UserIdentity $user, array $params = [] ): ?ProfileImageInfo {
		$this->generateFile( $user, $params );
		if ( $this->file ) {
			return new ProfileImageInfo(
				$this->file->getLocalCopyPath(),
				$this->file->getFile()->getMimeType()
			);
		}
		return null;
	}

	/**
	 * @param UserIdentity $user
	 * @param array $params
	 * @return void
	 * @throws MWException
	 */
	private function generateFile( UserIdentity $user, array $params ) {
		$file = $this->generator->getAvatarFile( $user );
		if ( !$file->exists() ) {
			$this->generator->generate( $user );
		}

		$this->file = $this->getThumbnailImageFile(
			$file, $params['width'] ?? 32, $params['height'] ?? 32
		);
	}

	/**
	 * @param File $file
	 * @param int $width
	 * @param int $height
	 * @return ThumbnailImage|null
	 */
	protected function getThumbnailImageFile( File $file, $width, $height ): ?ThumbnailImage {
		$params = [ 'width' => (int)$width ];

		if ( $file->getWidth() && $params[ 'width' ] >= $file->getWidth() ) {
			$params['width'] = $file->getWidth() - 1;
		}
		$height = (int)$height;
		if ( $height != -1 ) {
			$params['height'] = $height;
		}
		if (
			isset( $params['height'] ) &&
			$file->getHeight() && $params['height'] >= $file->getHeight()
		) {
			$params['height'] = $file->getHeight() - 1;
		}

		$transformation = $file->transform( $params );

		if ( !( $transformation instanceof ThumbnailImage ) ) {
			return null;
		}

		return $transformation;
	}

	/**
	 * @inheritDoc
	 */
	public function getRLModules(): array {
		return [ 'ext.bluespice.userProfile.generateImageProvider' ];
	}

	/**
	 * @inheritDoc
	 */
	public function unset( User $user ) {
		// NOOP
	}

	/**
	 * @inheritDoc
	 */
	public function getPriority(): int {
		return 2;
	}
}
