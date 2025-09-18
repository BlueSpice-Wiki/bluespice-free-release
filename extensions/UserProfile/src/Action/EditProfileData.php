<?php

namespace MediaWiki\Extension\UserProfile\Action;

use Article;
use EditAction;
use Exception;
use MediaWiki\Context\IContextSource;
use MediaWiki\Extension\UserProfile\ProfileManager;
use MediaWiki\Html\Html;
use MediaWiki\MediaWikiServices;
use PermissionsError;

class EditProfileData extends EditAction {

	/** @var ProfileManager */
	protected ProfileManager $profileManager;

	/**
	 * @param Article $article
	 * @param IContextSource|null $context
	 */
	public function __construct( Article $article, ?IContextSource $context = null ) {
		parent::__construct( $article, $context );
		$this->profileManager = MediaWikiServices::getInstance()->getService( 'UserProfile.Manager' );
	}

	/**
	 * @return string
	 */
	public function getName() {
		return 'editprofiledata';
	}

	/**
	 * @return void
	 * @throws PermissionsError
	 */
	public function show() {
		$this->useTransactionalTimeLimit();

		$title = $this->getTitle();
		$profileOwner = $this->profileManager->getUserFromTitle( $title );
		if ( !$profileOwner ) {
			throw new Exception( 'userprofile-error-invalid-page' );
		}
		$this->profileManager->assertActorCan( 'edit', $profileOwner, $this->getUser() );

		$out = $this->getOutput();
		$out->setPageTitle(
			$this->getContext()->msg( 'userprofile-editprofiledata-title', $profileOwner->getName() )->text()
		);
		$out->setRobotPolicy( 'noindex,nofollow' );
		$fields = $this->profileManager->getFieldRegistry()->getSerializedFields(
			$profileOwner, $this->getUser(), $this->getLanguage()
		);
		$data = $this->profileManager->getProfileData( $profileOwner, $this->getUser(), true );

		$out->addHTML(
			Html::element( 'div', [
				'id' => 'userprofile-editor',
				'data-user' => $profileOwner->getName(),
				'data-fields' => json_encode( $fields ),
				'data-data' => json_encode( $data ),
			] )
		);

		$out->addModules( [ 'ext.userProfile.editor' ] );
	}
}
