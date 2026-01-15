<?php

namespace BlueSpice\SaferEdit\Privacy;

use BlueSpice\Privacy\IPrivacyHandler;
use MediaWiki\Status\Status;
use MediaWiki\User\User;
use Wikimedia\Rdbms\IDatabase;

class Handler implements IPrivacyHandler {

	/** @var IDatabase */
	protected $db;

	/**
	 * @inheritDoc
	 */
	public function __construct( IDatabase $db ) {
		$this->db = $db;
	}

	/**
	 * @inheritDoc
	 */
	public function anonymize( $oldUsername, $newUsername ) {
		$this->db->update(
			'bs_saferedit',
			[ 'se_user_name' => $newUsername ],
			[ 'se_user_name' => $oldUsername ],
			__METHOD__
		);

		return Status::newGood();
	}

	/**
	 * @inheritDoc
	 */
	public function delete( User $userToDelete, User $deletedUser ) {
		return $this->anonymize( $userToDelete->getName(), $deletedUser->getName() );
	}

	/**
	 * @inheritDoc
	 */
	public function exportData( array $types, $format, User $user ) {
		// What would the information here be?
		return Status::newGood( [] );
	}
}
