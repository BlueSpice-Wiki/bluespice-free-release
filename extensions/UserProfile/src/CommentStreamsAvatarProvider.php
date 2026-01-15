<?php

namespace MediaWiki\Extension\UserProfile;

use MediaWiki\Extension\CommentStreams\SocialProfileInterface;
use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\DynamicFileDispatcher\DynamicFileDispatcherFactory;

class CommentStreamsAvatarProvider extends SocialProfileInterface {

	/**
	 * @var DynamicFileDispatcherFactory
	 */
	protected $dfdFactory;

	/**
	 * @param DynamicFileDispatcherFactory $dfdFactory
	 */
	public function __construct( DynamicFileDispatcherFactory $dfdFactory ) {
		$this->dfdFactory = $dfdFactory;
	}

	/**
	 * @param UserIdentity $user
	 * @return string|null
	 */
	public function getAvatar( UserIdentity $user ): ?string {
		return $this->dfdFactory->getUrl( 'userprofileimage', [
			'username' => $user->getName()
		] );
	}
}
