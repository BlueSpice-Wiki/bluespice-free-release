<?php

namespace BlueSpice\PageAssignments\Privacy;

use BlueSpice\Privacy\IPrivacyHandler;
use BlueSpice\Privacy\Module\Transparency;
use MediaWiki\Status\Status;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use Wikimedia\Rdbms\IDatabase;

class Handler implements IPrivacyHandler {

	/** @var IDatabase */
	protected $db;

	/**
	 *
	 * @param IDatabase $db
	 */
	public function __construct( IDatabase $db ) {
		$this->db = $db;
	}

	/**
	 *
	 * @param string $oldUsername
	 * @param string $newUsername
	 * @return Status
	 */
	public function anonymize( $oldUsername, $newUsername ) {
		$this->db->update(
			'bs_pageassignments',
			[ 'pa_assignee_key' => $newUsername ],
			[
				'pa_assignee_key' => $oldUsername,
				'pa_assignee_type' => 'user'
			],
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
		$this->db->delete(
			'bs_pageassignments',
			[ 'pa_assignee_key' => $userToDelete->getName() ],
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
			'bs_pageassignments',
			[ 'pa_page_id' ],
			[ 'pa_assignee_key' => $user->getName() ],
			__METHOD__
		);

		$titles = [];
		foreach ( $res as $row ) {
			$title = Title::newFromID( $row->pa_page_id );
			if ( $title instanceof Title === false ) {
				continue;
			}
			$titles[] = $title->getPrefixedText();
		}

		if ( empty( $titles ) ) {
			return Status::newGood( [] );
		}

		return Status::newGood( [
			Transparency::DATA_TYPE_WORKING => [
				wfMessage(
					'bs-pageassignments-privacy-transparency-working-assignments',
					implode( ', ', $titles ),
					count( $titles )
				)->text()
			]
		] );
	}
}
