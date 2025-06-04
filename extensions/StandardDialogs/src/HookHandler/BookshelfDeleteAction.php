<?php

namespace MediaWiki\Extension\StandardDialogs\HookHandler;

use BlueSpice\Bookshelf\Hook\BSBookshelfBooksOverviewBeforeSetBookActions;
use MediaWiki\Extension\StandardDialogs\BookOverviewActions\BookOverviewDelete;
use MediaWiki\Title\Title;

class BookshelfDeleteAction implements BSBookshelfBooksOverviewBeforeSetBookActions {

	public function onBSBookshelfBooksOverviewBeforeSetBookActions(
		array &$actions,
		Title $book,
		string $displayTitle
	): void {
		if ( !isset( $actions['delete'] ) ) {
			return;
		}

		$actions[ 'delete' ] = new BookOverviewDelete( $book, $displayTitle );
	}
}
