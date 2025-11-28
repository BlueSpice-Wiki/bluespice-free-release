<?php

use MediaWiki\Html\Html;
use MediaWiki\Json\FormatJson;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;

class PageAssignmentsWikiExplorerHooks {

	/**
	 *
	 * @param array $aFilters
	 * @param array &$aTables
	 * @param array &$aFields
	 * @param array &$aConditions
	 * @param array &$aJoinConditions
	 * @return bool
	 */
	public static function onWikiExplorerQueryPagesWithFilter( $aFilters, &$aTables,
		&$aFields, &$aConditions, &$aJoinConditions ) {
		$dbr = MediaWikiServices::getInstance()->getDBLoadBalancer()
			->getConnection( DB_REPLICA );
		$sTablePrefix = $dbr->tablePrefix();
		$tableName = $sTablePrefix . 'bs_pageassignments';
		$aTables[] = "$tableName";
		$aJoinConditions["$tableName"] = [
			'LEFT OUTER JOIN',
			"page_id=pa_page_id"
		];

		$aFields[] = "pa_assignee_key";

		return true;
	}

	/**
	 *
	 * @param array &$aRows
	 * @return bool
	 */
	public static function onWikiExplorerBuildDataSets( &$aRows ) {
		if ( !count( $aRows ) ) {
			return true;
		}

		foreach ( $aRows as $iKey => $aRowSet ) {
			$aRows[$iKey]['page_assignments'] = '';
		}

		$aPageIds = array_keys( $aRows );

		$services = MediaWikiServices::getInstance();
		$dbr = $services->getDBLoadBalancer()->getConnection( DB_REPLICA );
		$aTables = [
			'bs_pageassignments'
		];
		$sField = "pa_page_id, pa_position, pa_assignee_type, pa_assignee_key";
		$sCondition = "pa_page_id IN (" . implode( ',', $aPageIds ) . ")";
		$aOptions = [
			'ORDER BY' => 'pa_page_id, pa_position'
		];

		$oRes = $dbr->select(
			$aTables,
			$sField,
			$sCondition,
			__METHOD__,
			$aOptions
		);

		$userKeys = [];
		foreach ( $oRes as $row ) {
			if ( $row->pa_assignee_type == 'user' ) {
				$userKeys[] = $row->pa_assignee_key;
			}
		}

		$userKeys = array_unique( $userKeys );

		if ( $userKeys ) {
			$userNames = $dbr->select(
				'user',
				[ 'user_name', 'user_real_name' ],
				[ 'user_name' => $userKeys ],
				__METHOD__
			);

			$userNameToRealNameMap = [];
			foreach ( $userNames as $row ) {
				$userNameToRealNameMap[$row->user_name] = $row->user_real_name;
			}
		}

		$aData = [];
		$aUserIds = [];
		$userFactory = $services->getUserFactory();
		foreach ( $oRes as $oRow ) {
			if ( $oRow->pa_assignee_type == 'group' ) {
				$aData[$oRow->pa_page_id][] =
					'<li>' .
						'<i class="bs-icon-group"></i>' .
						'<a class="bs-pa-wikiexplorer-groups" href="#">' .
							static::makeGroupAssignmentLabel( $oRow->pa_assignee_key ) .
						'</a>' .
					'</li>';
				continue;
			}

			$oUser = $userFactory->newFromName( $oRow->pa_assignee_key );
			$userExists = isset( $userNameToRealNameMap[$oRow->pa_assignee_key] );
			if ( !$oUser || !$userExists ) {
				continue;
			}

			$aUserIds[$oRow->pa_page_id][] = $oUser->getId();
			if ( $userNameToRealNameMap[$oRow->pa_assignee_key] ) {
				$userRealName = $userNameToRealNameMap[$oRow->pa_assignee_key];
			} else {
				$userRealName = $oRow->pa_assignee_key;
			}

			$aData[$oRow->pa_page_id][] =
				'<li>' .
					'<i class="bs-icon-user"></i>' .
					'<a class="bs-pa-wikiexplorer-users" href="#">' .
						$userRealName .
					'</a>' .
				'</li>';
		}

		foreach ( $aRows as $iKey => $aRowSet ) {
			$aRows[$iKey]['page_assignments'] = '';
			if ( array_key_exists( $iKey, $aData ) ) {
				$aRows[$iKey]['page_assignments'] = Html::rawElement(
					'ul',
					[
						'class' => 'bs-wikiexplorer-list-field',
						'data-articleId' => $iKey,
						'data-assignees' => FormatJson::encode(
							$aUserIds[$iKey]
						)
					],
					implode( '', $aData[$iKey] )
				);
			}
		}

		return true;
	}

	/**
	 * Copy of `BlueSpice\PageAssignments\Assignment::getText`. Redundant
	 * implemenation for performance reasons
	 * @param string $groupname
	 * @return string
	 */
	private static function makeGroupAssignmentLabel( $groupname ) {
		return Message::newFromKey( "group-{$groupname}" )->exists()
					? Message::newFromKey( "group-{$groupname}" )->text()
					: $groupname;
	}
}
