<?php

namespace BlueSpice\Avatars\Privacy;

use BlueSpice\Avatars\Extension as Avatars;
use BlueSpice\Avatars\Generator;
use BlueSpice\Privacy\IPrivacyHandler;
use BlueSpice\Privacy\Module\Transparency;
use Exception;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\Status\Status;
use MediaWiki\User\User;
use Wikimedia\Rdbms\IDatabase;

class Handler implements IPrivacyHandler {
	/** @var IDatabase */
	protected $db;

	/** @var MediaWikiServices */
	protected $services = null;

	/**
	 * @param IDatabase $db
	 */
	public function __construct( IDatabase $db ) {
		$this->db = $db;
		$this->services = MediaWikiServices::getInstance();
	}

	/**
	 * @param User $userToDelete
	 * @param User $deletedUser
	 * @return Status
	 */
	public function delete( User $userToDelete, User $deletedUser ) {
		Avatars::unsetUserImage( $userToDelete );
		return Status::newGood();
	}

	/**
	 * @param array $types Types of info users wants to retrieve
	 * @param string $format Requested output format
	 * @param User $user User to export data from
	 * @return Status
	 */
	public function exportData( array $types, $format, User $user ) {
		$config = $this->services->getConfigFactory()->makeConfig( 'bsg' );
		$dfdUrlBuilder = $this->services->getService(
			'MWStake.DynamicFileDispatcher.Factory'
		);
		$url = $dfdUrlBuilder->getUrl(
			'userprofileimage',
			[
				'username' => $user->getName(),
				'width' => 200,
				'height' => 52000,
			]
		);
		$label = Message::newFromKey( 'bs-avatars-upload-label' );
		return Status::newGood( [
			Transparency::DATA_TYPE_PERSONAL => [
				"{$label->plain()}: {$config->get( 'Server' )}$url"
			]
		] );
	}

	/**
	 * @param string $oldUsername
	 * @param string $newUsername
	 * @return Status
	 */
	public function anonymize( $oldUsername, $newUsername ) {
		$user = $this->services->getUserFactory()->newFromName( $oldUsername );
		$user->setName( $newUsername );

		$generator = $this->services->getService( 'BSAvatarsAvatarGenerator' );
		try {
			$generator->generate( $user, [ Generator::PARAM_OVERWRITE => true ] );
		} catch ( Exception $ex ) {
			return Status::newFatal( $ex->getMessage() );
		}

		return Status::newGood();
	}
}
