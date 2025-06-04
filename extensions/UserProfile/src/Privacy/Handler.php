<?php

namespace MediaWiki\Extension\UserProfile\Privacy;

use BlueSpice\Privacy\IPrivacyHandler;
use BlueSpice\Privacy\Module\Transparency;
use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\UserProfile\ProfileManager;
use MediaWiki\MediaWikiServices;
use MediaWiki\Status\Status;
use MediaWiki\User\User;
use Wikimedia\Rdbms\IDatabase;

class Handler implements IPrivacyHandler {
	/**
	 *
	 * @var User
	 */
	protected $user;

	/** @var ProfileManager */
	private $profileManager;

	/**
	 *
	 * @var UserFactory
	 */
	private $userFactory;

	/**
	 *
	 * @param IDatabase $db
	 */
	public function __construct( IDatabase $db ) {
		$this->userFactory = MediaWikiServices::getInstance()->getUserFactory();
		$this->profileManager = MediaWikiServices::getInstance()->getService( 'UserProfile.Manager' );
	}

	/**
	 *
	 * @param string $oldUsername
	 * @param string $newUsername
	 * @return Status
	 */
	public function anonymize( $oldUsername, $newUsername ) {
		// We can delete data, but old data will still be in the history
		$this->user = $this->userFactory->newFromName( $newUsername );
		try {
			$this->profileManager->setProfileData(
				[], $this->user, User::newSystemUser( 'MediaWiki default', [ 'steal' => true ] )
			);
		} catch ( \Throwable $ex ) {
			return Status::newFatal( $ex->getMessage() );
		}

		return Status::newGood();
	}

	/**
	 *
	 * @param User $userToDelete
	 * @param User $deletedUser
	 * @return Status
	 */
	public function delete( User $userToDelete, User $deletedUser ) {
		// Privacy handles page deletion by default
		return Status::newGood();
	}

	/**
	 *
	 * @param array $types
	 * @param string $format
	 * @param User $user
	 * @return Status
	 */
	public function exportData( array $types, $format, User $user ) {
		$fields = $this->profileManager->getFieldRegistry()->getSerializedFields(
			$user, User::newSystemUser( 'MediaWiki default', [ 'steal' => true ] ),
			RequestContext::getMain()->getLanguage()
		);
		$data = $this->profileManager->getProfileData(
			$user,
			User::newSystemUser( 'MediaWiki default', [ 'steal' => true ] )
		);
		$finalData = [];
		foreach ( $fields as $key => $value ) {
			if ( isset( $data[$key] ) ) {
				$finalData[$key] = $value['label'] . ':' . $data[$key];
			}
		}
		return Status::newGood( [
			Transparency::DATA_TYPE_PERSONAL => $finalData
		] );
	}
}
