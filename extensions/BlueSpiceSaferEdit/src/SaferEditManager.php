<?php

namespace BlueSpice\SaferEdit;

use BlueSpice\ExtensionAttributeBasedRegistry;
use MediaWiki\Context\IContextSource;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Status\Status;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use Wikimedia\Rdbms\ILoadBalancer;

class SaferEditManager {

	/** @var ILoadBalancer */
	protected $lb;

	/** @var IContextSource */
	protected $context;

	/** @var ExtensionAttributeBasedRegistry */
	protected $checkerRegistry;

	/** @var PermissionManager */
	protected $permissionManager;

	/**
	 * @param ILoadBalancer $lb
	 * @param IContextSource $context
	 * @param ExtensionAttributeBasedRegistry $checkerRegistry
	 * @param PermissionManager $permissionManager
	 */
	public function __construct(
		ILoadBalancer $lb, IContextSource $context,
		ExtensionAttributeBasedRegistry $checkerRegistry,
		PermissionManager $permissionManager
	) {
		$this->context = $context;
		$this->lb = $lb;
		$this->checkerRegistry = $checkerRegistry;
		$this->permissionManager = $permissionManager;
	}

	/**
	 * @param User $user
	 * @param Title $title
	 * @param int $section
	 * @return Status
	 */
	public function saveUserEditing( User $user, Title $title, $section = -1 ) {
		if ( !$this->permissionManager->userCan( 'edit', $user, $title ) ) {
			return Status::newFatal( "User cannot edit the page" );
		}

		$dbr = $this->lb->getConnection( DB_REPLICA );
		$table = 'bs_saferedit';
		$fields = [
			"se_timestamp" => $dbr->timestamp( wfTimestamp( TS_MW, time() ) )
		];
		$conditions = [
			"se_user_name" => $user->getName(),
			"se_page_title" => $title->getDBkey(),
			"se_page_namespace" => $title->getNamespace(),
			"se_edit_section" => $section,
		];
		// needed for update reason
		$options = [
			'ORDER BY' => 'se_id DESC',
			'LIMIT' => 1,
		];

		$row = $dbr->selectRow(
			$table,
			[ 'se_id' ],
			$conditions,
			__METHOD__,
			$options
		);
		if ( $row ) {
			$title->invalidateCache();
			$updateOk = $this->lb->getConnection( DB_PRIMARY )->update(
				$table,
				$fields,
				[ "se_id" => $row->se_id ],
				__METHOD__
			);
			if ( $updateOk ) {
				return Status::newGood();
			}
		} else {
			$title->invalidateCache();
			$insertOk = $this->lb->getConnection( DB_PRIMARY )->insert(
				$table,
				$conditions + $fields,
				__METHOD__
			);
			if ( $insertOk ) {
				return Status::newGood();
			}
		}

		return Status::newFatal( "Failed to save editing information" );
	}

	/**
	 * Actually delete all stored saves for a user
	 *
	 * @param User $user User that edited a page
	 * @param Title $title
	 * @return Status
	 */
	public function doClearSaferEdit( User $user, Title $title ) {
		$deleteOk = $this->lb->getConnection( DB_PRIMARY )->delete(
			'bs_saferedit',
			[
				"se_user_name" => $user->getName(),
				"se_page_title" => $title->getDBkey(),
				"se_page_namespace" => $title->getNamespace(),
			],
			__METHOD__
		);

		if ( $deleteOk ) {
			$title->invalidateCache();
			return Status::newGood();
		}

		return Status::newFatal( "Deletion failed" );
	}

	/**
	 * @param string $func Function to be executed
	 * @param mixed &$result
	 */
	public function askEnvironmentalCheckers( $func, &$result ) {
		foreach ( $this->checkerRegistry->getAllKeys() as $key ) {
			$callable = $this->checkerRegistry->getValue( $key );
			if ( !is_callable( $callable ) ) {
				continue;
			}
			$provider = call_user_func( $callable, $this->context );
			if ( !$provider instanceof IEnvironmentChecker ) {
				continue;
			}
			if ( !$provider->$func( $result ) ) {
				return;
			}
		}
	}
}
