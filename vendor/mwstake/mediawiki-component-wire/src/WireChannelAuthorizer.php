<?php

namespace MWStake\MediaWiki\Component\Wire;

use MediaWiki\Permissions\Authority;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\SpecialPage\SpecialPageFactory;
use MediaWiki\Title\TitleFactory;

class WireChannelAuthorizer {

	/**
	 * @param TitleFactory $titleFactory
	 * @param SpecialPageFactory $specialPageFactory
	 * @param PermissionManager $permissionManager
	 */
	public function __construct(
		private readonly TitleFactory $titleFactory,
		private readonly SpecialPageFactory $specialPageFactory,
		private readonly PermissionManager $permissionManager
	) {
	}

	/**
	 * @param string $channel
	 * @param Authority $authority
	 * @return bool
	 */
	public function canUserSubscribeToChannel(
		string $channel, Authority $authority
	) {
		if ( str_starts_with( $channel, 'page-' ) ) {
			// For page channels, we need to check if the user has read access to the page
			$pageKey = substr( $channel, 5 );
			[ $namespace, $title ] = explode( '|', $pageKey, 2 );
			$titleObj = $this->titleFactory->makeTitle( $namespace, $title );
			return $this->permissionManager->userCan( 'read', $authority->getUser(), $titleObj );
		}
		if ( str_starts_with( $channel, 'special-' ) ) {
			// For special pages, we check if the user can view the special page
			$specialPageName = substr( $channel, 8 );
			$specialPage = $this->specialPageFactory->getPage( $specialPageName );
			if ( !$specialPage ) {
				return false;
			}
			$restriction = $specialPage->getRestriction();
			if ( !$restriction ) {
				return true;
			}
			return $this->permissionManager->userHasRight( $authority->getUser(), $restriction );
		}
		return true;
	}
}
