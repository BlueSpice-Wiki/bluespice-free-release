<?php

namespace MediaWiki\Extension\StandardDialogs\BookOverviewActions;

use BlueSpice\Bookshelf\BooksOverviewActions\Delete;

class BookOverviewDelete extends Delete {

	/**
	 * @return string
	 */
	public function getHref(): string {
		return '';
	}

	/**
	 * @return array
	 */
	public function getRLModules(): array {
		return [ 'ext.standardDialogs.books.deleteAction' ];
	}
}
