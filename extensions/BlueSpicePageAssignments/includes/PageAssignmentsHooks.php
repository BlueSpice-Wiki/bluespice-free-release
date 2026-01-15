<?php

use MediaWiki\Content\Content;
use MediaWiki\MediaWikiServices;
use MediaWiki\Status\Status;
use MediaWiki\User\User;

class PageAssignmentsHooks {

	/**
	 * Clears assignments
	 * @param WikiPage &$wikiPage
	 * @param User &$user
	 * @param string $reason
	 * @param int $id
	 * @param Content $content
	 * @param ManualLogEntry $logEntry
	 * @return bool
	 */
	public static function onArticleDeleteComplete( &$wikiPage, &$user, $reason,
		$id, $content, $logEntry ) {
		$dbw = MediaWikiServices::getInstance()->getDBLoadBalancer()
			->getConnection( DB_PRIMARY );
		$dbw->delete(
			'bs_pageassignments',
			[ 'pa_page_id' => $wikiPage->getId() ],
			__METHOD__
		);
		return true;
	}

	/**
	 * Deletes all page assignments on user deleted.
	 * @param UserManager $oUserManager
	 * @param User $oUser
	 * @param Status &$oStatus
	 * @param User $oPerformer
	 * @return bool
	 */
	public static function onBSUserManagerAfterDeleteUser( $oUserManager, $oUser,
		&$oStatus, $oPerformer ) {
		$dbw = MediaWikiServices::getInstance()->getDBLoadBalancer()
			->getConnection( DB_PRIMARY );
		$dbw->delete(
			'bs_pageassignments',
			[
				'pa_assignee_key' => $oUser->getName(),
				'pa_assignee_type' => 'user'
			],
			__METHOD__
		);
		return true;
	}

	/**
	 * Updates all page assignments on group name change.
	 * @param string $sGroup
	 * @param string $sNewGroup
	 * @return bool
	 */
	public static function onBSGroupManagerGroupNameChanged( $sGroup, $sNewGroup ) {
		$dbw = MediaWikiServices::getInstance()->getDBLoadBalancer()
			->getConnection( DB_PRIMARY );
		$dbw->update(
			'bs_pageassignments',
			[
				'pa_assignee_key' => $sNewGroup,
			],
			[
				'pa_assignee_key' => $sGroup,
				'pa_assignee_type' => 'group'
			],
			__METHOD__
		);
		return true;
	}

	/**
	 * Deletes all page assignments on group deleted.
	 * @param string $sGroup
	 * @return bool
	 */
	public static function onBSGroupManagerGroupDeleted( $sGroup ) {
		$dbw = MediaWikiServices::getInstance()->getDBLoadBalancer()
			->getConnection( DB_PRIMARY );
		$dbw->delete(
			'bs_pageassignments',
			[
				'pa_assignee_key' => $sGroup,
				'pa_assignee_type' => 'group'
			],
			__METHOD__
		);
		return true;
	}

}
