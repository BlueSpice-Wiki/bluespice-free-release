<?php

namespace BlueSpice\Avatars\UserProfile\ImageProvider;

use MediaWiki\Extension\UserProfile\ProfileImage\IProfileImageProvider;

class UploadImage extends GenerateImage implements IProfileImageProvider {

	/**
	 * @inheritDoc
	 */
	public function getRLModules(): array {
		return [ 'ext.bluespice.userProfile.uploadImageProvider' ];
	}

	/**
	 * @inheritDoc
	 */
	public function getPriority(): int {
		return 1;
	}
}
