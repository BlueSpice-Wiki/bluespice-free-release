<?php

namespace MediaWiki\Extension\UserProfile\Hooks;

use BlueSpice\Discovery\Hook\BlueSpiceDiscoveryTemplateDataProviderAfterInit;
use MediaWiki\Extension\UserProfile\ProfileManager;
use MediaWiki\Hook\SkinTemplateNavigation__UniversalHook;
use MediaWiki\Html\Html;
use MediaWiki\Output\Hook\BeforePageDisplayHook;
use MediaWiki\Output\Hook\OutputPageBeforeHTMLHook;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use MediaWiki\User\UserFactory;

// phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName
class RenderProfileSlot implements
	OutputPageBeforeHTMLHook,
	SkinTemplateNavigation__UniversalHook,
	BlueSpiceDiscoveryTemplateDataProviderAfterInit,
	BeforePageDisplayHook
{

	/** @var PermissionManager */
	private $permissionManager;

	/** @var UserFactory */
	private $userFactory;

	/** @var ProfileManager */
	private $profileManager;

	/** @var User|null */
	private $profileOwner = null;

	/**
	 * @param PermissionManager $permissionManager
	 * @param UserFactory $userFactory
	 * @param ProfileManager $profileManager
	 */
	public function __construct(
		PermissionManager $permissionManager, UserFactory $userFactory, ProfileManager $profileManager
	) {
		$this->permissionManager = $permissionManager;
		$this->userFactory = $userFactory;
		$this->profileManager = $profileManager;
	}

	/**
	 * @inheritDoc
	 */
	public function onBeforePageDisplay( $out, $skin ): void {
		if ( $this->profileOwner && $this->profileOwner->getRealName() ) {
			$out->setPageTitle( $this->profileOwner->getRealName() );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function onOutputPageBeforeHTML( $out, &$text ): void {
		$this->profileOwner = null;
		$action = $out->getRequest()->getVal( 'action', 'view' );
		if ( !$out->getTitle() || $out->getTitle()->getNamespace() !== NS_USER || $action !== 'view' ) {
			return;
		}
		if ( !$this->isBasePage( $out->getTitle() ) ) {
			return;
		}
		if ( !$this->permissionManager->userCan( 'read', $out->getUser(), $out->getTitle() ) ) {
			return;
		}
		$owner = $this->userFactory->newFromName( $out->getTitle()->getText() );
		if ( !$owner ) {
			return;
		}
		$this->profileOwner = $owner;

		$own = $this->isOwnProfile( $out->getUser(), $owner );
		$profileHtml = Html::element( 'div', [
			'class' => 'user-profile-on-user-page',
			'data-params' => json_encode( [
				'user' => $owner->getName(),
				'user-display' => $owner->getRealName() ?: $owner->getName(),
				'editable' => $this->profileManager->canEdit( $owner, $out->getUser() ),
				'own' => $own
			] ),
			'style' => 'min-height: 220px'
		] );

		$text = $profileHtml . $text;
		$out->addModules( [ 'ext.userProfile.main' ] );
	}

	/**
	 * @param Title $title
	 * @return bool
	 */
	private function isBasePage( Title $title ): bool {
		return $title->getBaseText() === $title->getText();
	}

	/**
	 * @param User $user
	 * @param User $owner
	 * @return bool
	 */
	private function isOwnProfile( User $user, User $owner ): bool {
		return $user->isRegistered() && $user->getId() === $owner->getId();
	}

	/**
	 * @inheritDoc
	 */
	public function onBlueSpiceDiscoveryTemplateDataProviderAfterInit( $registry ): void {
		$registry->register( 'panel/edit', 'ca-editprofiledata' );
	}

	/**
	 * @inheritDoc
	 */
	public function onSkinTemplateNavigation__Universal( $sktemplate, &$links ): void {
		if (
			!$sktemplate->getTitle() ||
			$sktemplate->getTitle()->getNamespace() !== NS_USER ||
			$sktemplate->getTitle()->isSubpage()
		) {
			return;
		}
		$owner = $this->userFactory->newFromName( $sktemplate->getTitle()->getText() );
		if ( !$this->profileManager->canEdit( $owner, $sktemplate->getUser() ) ) {
			return;
		}
		$links['views']['editprofiledata'] = [
			'text' => $sktemplate->getContext()->msg( 'userprofile-edit-profile' )->text(),
			'title' => $sktemplate->getContext()->msg( 'userprofile-edit-profile' )->text(),
			'href' => $sktemplate->getTitle()->getLocalURL( [
				'action' => 'editprofiledata',
			] )
		];
	}
}
