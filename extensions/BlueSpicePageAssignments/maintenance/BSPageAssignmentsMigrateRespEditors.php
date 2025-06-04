<?php

use MediaWiki\Maintenance\LoggedUpdateMaintenance;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;

$extDir = dirname( dirname( __DIR__ ) );

require_once "$extDir/BlueSpiceFoundation/maintenance/BSMaintenance.php";

class BSPageAssignmentsMigrateRespEditors extends LoggedUpdateMaintenance {
	protected function doDBUpdates() {
		$aRespEditors = $this->getResponsibleEditors();
		$this->output( "BSPageAssignments: Migrate Responsible Editors..." );
		if ( empty( $aRespEditors ) ) {
			$this->output( "OK\n" );
			return true;
		}

		$iRespEditors = count( $aRespEditors );
		$this->output( "($iRespEditors)..." );
		$this->output( "\n => " );
		foreach ( $aRespEditors as $aRespEditor ) {
			try {
				$this->insertAssignment( $aRespEditor );
			} catch ( Exception $e ) {
				$this->output( "f" );
				continue;
			}
			$this->output( "." );
		}

		$this->output( "OK\n" );
		return true;
	}

	/**
	 *
	 * @return string
	 */
	protected function getUpdateKey() {
		return 'bs-pageassignments-migrate-responsible-editors';
	}

	/**
	 *
	 * @param array $aReturn
	 * @return array
	 */
	protected function getResponsibleEditors( $aReturn = [] ) {
		$aOptions = [
			'LIMIT' => 99999,
		];
		$oRes = $this->getDB( DB_REPLICA )->select(
			'bs_responsible_editors',
			'*',
			[],
			__METHOD__,
			$aOptions
		);
		$userFactory = MediaWikiServices::getInstance()->getUserFactory();
		foreach ( $oRes as $oRow ) {
			$oUser = $userFactory->newFromId( $oRow->re_user_id );
			if ( !$oUser || $oUser->isAnon() ) {
				continue;
			}
			$title = Title::newFromId( (int)$oRow->re_page_id );
			if ( !$title ) {
				continue;
			}
			if ( !$title->exists() ) {
				continue;
			}
			$assignment = [
				'pa_assignee_key' => $oUser->getName(),
				'pa_page_id' => (int)$oRow->re_page_id,
				'pa_assignee_type' => 'user',
			];
			if ( $this->assignmentExists( $assignment ) !== false ) {
				continue;
			}
			$aReturn[] = $assignment;
		}
		return $aReturn;
	}

	/**
	 *
	 * @param array $aRespEditor
	 * @return true
	 */
	protected function insertAssignment( $aRespEditor ) {
		$this->getDB( DB_PRIMARY )->insert(
			'bs_pageassignments',
			$aRespEditor,
			__METHOD__
		);

		return true;
	}

	/**
	 *
	 * @param array $conds
	 * @return bool
	 */
	protected function assignmentExists( $conds ) {
		return $this->getDB( DB_PRIMARY )->selectRow(
			'bs_pageassignments',
			'*',
			$conds,
			__METHOD__
		);
	}

}
