<?php

namespace MediaWiki\Extension\UserProfile\Hooks;

use MediaWiki\Extension\UserProfile\ProfileFieldRegistry;
use MediaWiki\Extension\UserProfile\Tag\UserProfileTag;
use MediaWiki\User\UserFactory;
use MWStake\MediaWiki\Component\GenericTagHandler\Hook\MWStakeGenericTagHandlerInitTagsHook;

class AddTag implements MWStakeGenericTagHandlerInitTagsHook {

	/**
	 * @param UserFactory $userFactory
	 * @param ProfileFieldRegistry $profileFieldRegistry
	 */
	public function __construct(
		private readonly UserFactory $userFactory,
		private readonly ProfileFieldRegistry $profileFieldRegistry
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function onMWStakeGenericTagHandlerInitTags( array &$tags ) {
		$tags[] = new UserProfileTag( $this->userFactory, $this->profileFieldRegistry );
	}
}
