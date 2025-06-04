<?php

namespace MediaWiki\Extension\UserProfile\Hooks;

use MediaWiki\Extension\UserProfile\CommentStreamsAvatarProvider;
use MediaWiki\Hook\MediaWikiServicesHook;
use MediaWiki\MediaWikiServices;

class CommentStreamsAvatar implements MediaWikiServicesHook {

	/**
	 * @param MediaWikiServices $services
	 * @return void
	 */
	public function onMediaWikiServices( $services ) {
		if ( !$services->hasService( 'CommentStreamsSocialProfileInterface' ) ) {
			return;
		}
		$services->redefineService( 'CommentStreamsSocialProfileInterface', static function ( $services ) {
			return new CommentStreamsAvatarProvider(
				$services->getService( 'MWStake.DynamicFileDispatcher.Factory' )
			);
		} );
	}
}
