<?php

use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;

class PageAssignmentsBookmakerHooks {

	/**
	 * Adds information about assignments to the Bookshelf BookManager grid
	 * @param Title $oBookTitle
	 * @param stdClass $oBookRow
	 * @return bool
	 */
	public static function onBSBookshelfManagerGetBookDataRow( $oBookTitle, $oBookRow ) {
		$oBookRow->assignments = [];
		$aTexts = [];

		$target = static::getFactory()->newFromTargetTitle( $oBookTitle );
		if ( $target instanceof \BlueSpice\PageAssignments\ITarget === false ) {
			return true;
		}

		foreach ( $target->getAssignments() as $assignment ) {
			$oBookRow->assignments[] = $assignment->toStdClass();
			$aTexts[] = $assignment->getText();
		}
		$oBookRow->flat_assignments = implode( '', $aTexts );
		return true;
	}

	/**
	 *
	 * @return BlueSpice\PageAssignments\AssignmentFactory
	 */
	private static function getFactory() {
		return MediaWikiServices::getInstance()->getService(
			'BSPageAssignmentsAssignmentFactory'
		);
	}
}
