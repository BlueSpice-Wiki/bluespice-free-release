<?php

namespace BlueSpice\Discovery\HookHandler\SidebarBeforeOutput;

use MediaWiki\Hook\SidebarBeforeOutputHook;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\Title\Title;
use Skin;
use Throwable;

class AddSidebarLinks implements SidebarBeforeOutputHook {

	/**
	 *
	 * @var PermissionManager
	 */
	private $permissionManager = null;

	/**
	 *
	 * @param PermissionManager $permissionManager
	 */
	public function __construct( PermissionManager $permissionManager ) {
		$this->permissionManager = $permissionManager;
	}

	/**
	 *
	 * @param Skin $skin
	 * @param array &$sidebar
	 * @return void
	 */
	public function onSidebarBeforeOutput( $skin, &$sidebar ): void {
		if ( !empty( $sidebar['TOOLBOX']['permalink'] ) ) {
			$sidebar['TOOLBOX']['permalink']['text'] = $skin->msg( 'bs-discovery-permalink-copy-text' );
		}

		$user = $skin->getUser();
		$title = $skin->getSkin()->getTitle();
		if ( $title instanceof Title === false ) {
			return;
		}

		try {
			// `WebRequest::getRequestURL` may fail in some cases (e.g. UnitTests)
			$requestUrl = $skin->getConfig()->get( 'Server' )
				. $skin->getRequest()->getRequestURL();
		} catch ( Throwable $ex ) {
			$requestUrl = $title->getLocalURL();
		}

		$subject = $skin->msg( 'bs-discovery-sidebar-secondary-share-sharebymail-subject' );
		$subject->params(
			$skin->getConfig()->get( 'Sitename' ),
			$title->getFullText()
		);
		$subject = rawurlencode( $subject->text() );

		$body = $skin->msg( 'bs-discovery-sidebar-secondary-share-sharebymail-body' );
		$body->params(
			$skin->getConfig()->get( 'Sitename' ),
			$title->getFullText(),
			$requestUrl
		);
		$body = rawurlencode( $body->text() );

		$sidebar['TOOLBOX']['sharebymail'] = [
			'id' => 't-sharebymail',
			'text' => $skin->msg( 'bs-discovery-sidebar-secondary-share-sharebymail-text' ),
			'title' => $skin->msg( 'bs-discovery-sidebar-secondary-share-sharebymail-title' ),
			'href' => "mailto:?subject=$subject&body=$body",
			'class' => 'sharebymail'
		];

		if ( $this->permissionManager->userHasRight( $user, 'createpage' ) ) {
			$sidebar['TOOLBOX']['new-file'] = [
				'id' => 't-new-file',
				'text' => $skin->msg( 'bs-discovery-create-button-new-file-text' ),
				'title' => $skin->msg( 'bs-discovery-create-button-new-file-title' ),
				'href' => SpecialPage::getTitleFor( 'Upload' )->getLocalURL(),
				'class' => 'new-file'
			];
		}
	}
}
