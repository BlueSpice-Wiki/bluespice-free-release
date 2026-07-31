<?php

namespace BlueSpice\InterWikiLinks;

use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;

class Extension extends \BlueSpice\Extension {

	/**
	 * @param string $iwPrefix
	 */
	public static function purgeTitles( $iwPrefix ) {
		$dbr = MediaWikiServices::getInstance()->getDBLoadBalancer()
			->getConnection( DB_REPLICA );
		$res = $dbr->select(
			'iwlinks',
			[ 'iwl_from', 'iwl_prefix' ],
			[ 'iwl_prefix' => $iwPrefix ],
			__METHOD__
		);

		foreach ( $res as $row ) {
			$title = Title::newFromID( $row->iwl_from );
			if ( $title instanceof Title == false ) {
				continue;
			}
			$title->invalidateCache();
		}
	}
}
