<?php

namespace BlueSpice\PageAssignments\HookHandler;

use BlueSpice\Bookshelf\Hook\BSBookshelfBooksOverviewBeforeSetBookActions;
use BlueSpice\PageAssignments\BookOverviewActions\Assignments;
use MediaWiki\Title\Title;

class BSBookshelf implements BSBookshelfBooksOverviewBeforeSetBookActions {

	/**
	 * @param array &$actions
	 * @param Title $book
	 * @param string $displayTitle
	 * @return void
	 */
	public function onBSBookshelfBooksOverviewBeforeSetBookActions(
		array &$actions, Title $book, string $displayTitle
	): void {
		$actions['assignments'] = new Assignments( $book, $displayTitle );
	}
}
