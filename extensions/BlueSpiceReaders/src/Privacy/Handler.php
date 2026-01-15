<?php

namespace BlueSpice\Readers\Privacy;

use BlueSpice\Privacy\IPrivacyHandler;
use BlueSpice\Privacy\Module\Transparency;
use MediaWiki\Context\RequestContext;
use MediaWiki\Language\Language;
use MediaWiki\Status\Status;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use Wikimedia\Rdbms\IDatabase;

class Handler implements IPrivacyHandler {
	/** @var IDatabase */
	protected $db;
	/** @var Language|null */
	protected $language;

	/**
	 *
	 * @param IDatabase $db
	 */
	public function __construct( IDatabase $db ) {
		$this->db = $db;
		$this->language = RequestContext::getMain()->getLanguage();
	}

	/**
	 *
	 * @param string $oldUsername
	 * @param string $newUsername
	 * @return Status
	 */
	public function anonymize( $oldUsername, $newUsername ) {
		$this->db->update(
			'bs_readers',
			[ 'readers_user_name' => $newUsername ],
			[ 'readers_user_name' => $oldUsername ],
			__METHOD__
		);

		return Status::newGood();
	}

	/**
	 *
	 * @param User $userToDelete
	 * @param User $deletedUser
	 * @return Status
	 */
	public function delete( User $userToDelete, User $deletedUser ) {
		$this->anonymize( $userToDelete->getName(), $deletedUser->getName() );
		$this->db->update(
			'bs_readers',
			[ 'readers_user_id' => $deletedUser->getId() ],
			[ 'readers_user_id' => $userToDelete->getId() ],
			__METHOD__
		);

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
		if ( !in_array( Transparency::DATA_TYPE_WORKING, $types ) ) {
			return Status::newGood( [] );
		}

		$res = $this->db->select(
			'bs_readers',
			'*',
			[ 'readers_user_id' => $user->getId() ],
			__METHOD__
		);

		$data = [];
		foreach ( $res as $row ) {
			$title = Title::newFromID( $row->readers_page_id );
			if ( $title instanceof Title === false ) {
				continue;
			}

			$timestamp = $this->language->userTimeAndDate(
				$row->readers_ts,
				$user
			);

			$data[] = wfMessage(
				'bs-readers-privacy-transparency-working-readers',
				$title->getPrefixedText(),
				$timestamp,
				$user->getName()
			);
		}

		if ( empty( $data ) ) {
			return Status::newGood( [] );
		}

		return Status::newGood( [
			Transparency::DATA_TYPE_WORKING => $data
		] );
	}
}
