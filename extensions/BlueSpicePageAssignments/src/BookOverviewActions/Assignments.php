<?php

namespace BlueSpice\PageAssignments\BookOverviewActions;

use BlueSpice\Bookshelf\IBooksOverviewAction;
use MediaWiki\Message\Message;
use MediaWiki\Title\Title;

class Assignments implements IBooksOverviewAction {

	/**
	 * @var Title
	 */
	private $book = null;

	/**
	 * @var string
	 */
	private $displayTitle = '';

	/**
	 * @param Title $book
	 * @param string $displayTitle
	 */
	public function __construct( Title $book, string $displayTitle ) {
		$this->book = $book;
		$this->displayTitle = $displayTitle;
	}

	/**
	 * @return string
	 */
	public function getName(): string {
		return 'assignments';
	}

	/**
	 * @return int
	 */
	public function getPosition(): int {
		return 80;
	}

	/**
	 * @return array
	 */
	public function getClasses(): array {
		return [ 'pageassignments-book-overview' ];
	}

	/**
	 * @return array
	 */
	public function getIconClasses(): array {
		return [ 'bi-file-earmark-person' ];
	}

	/**
	 * @return Message
	 */
	public function getText(): Message {
		return new Message( 'bs-pageassignments-books-overview-page-book-action-assignment-text' );
	}

	/**
	 * @return Message
	 */
	public function getTitle(): Message {
		$titleText = $this->book->getPrefixedText();
		if ( $this->displayTitle !== '' ) {
			$titleText = $this->displayTitle;
		}
		return new Message(
			'bs-pageassignments-books-overview-page-book-action-assignment-title',
			[ $titleText ]
		);
	}

	/**
	 * @return string
	 */
	public function getHref(): string {
		return '';
	}

	/**
	 * @return string
	 */
	public function getRequiredPermission(): string {
		return 'pageassignments';
	}

	/**
	 * @return string
	 */
	public function getRLModules(): array {
		return [ 'ext.pageassignments.bookshelf.overviewAction' ];
	}
}
