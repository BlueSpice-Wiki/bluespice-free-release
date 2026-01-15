<?php

namespace BlueSpice\PageAssignments;

use MediaWiki\MediaWikiServices;

class ClientConfig {

	/**
	 * @return array
	 */
	public static function makeConfigJson(): array {
		$deps = [];
		MediaWikiServices::getInstance()->getHookContainer()->run( 'BSPageAssignmentsOverview', [
			&$deps
		] );

		return [
			'pageAssignmentsOverviewDeps' => $deps
		];
	}
}
