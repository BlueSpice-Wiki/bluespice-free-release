<?php

namespace BlueSpice\InsertCategory\HookHandler;

use BlueSpice\Api\Store;
use BlueSpice\Api\Store\Categorylinks;
use BlueSpice\InsertCategory\CategoryManipulatorFactory;
use BlueSpice\InsertCategory\Content\CategoryContent;
use MediaWiki\Content\Hook\ContentAlterParserOutputHook;
use MediaWiki\Content\WikitextContent;
use MediaWiki\Page\WikiPageFactory;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Title\TitleFactory;
use MWStake\MediaWiki\Component\DataStore\ResultSet;
use MWStake\MediaWiki\Component\DataStore\Schema;

class ApplyCategoriesFromCategorySlot implements ContentAlterParserOutputHook {

	/**
	 * @param TitleFactory $titleFactory
	 * @param WikiPageFactory $wikiPageFactory
	 * @param CategoryManipulatorFactory $categoryManipulatorFactory
	 */
	public function __construct(
		private readonly TitleFactory $titleFactory,
		private readonly WikiPageFactory $wikiPageFactory,
		private readonly CategoryManipulatorFactory $categoryManipulatorFactory
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function onContentAlterParserOutput( $content, $title, $parserOutput ) {
		if ( $content instanceof WikitextContent ) {
			// Can handle its own categories
			return;
		}
		if ( $content instanceof CategoryContent ) {
			$categories = $content->getCategories();
			if ( !empty( $categories ) ) {
				foreach ( $categories as $category ) {
					// Add categories to parser output
					$parserOutput->addCategory( $category );
				}
			}
		}
	}

	/**
	 * Make sure that categories coming from `category_storage` slot are marked as explicit
	 * Not a great solution though, should rewrite some more things
	 *
	 * @param Store $store
	 * @param ResultSet &$resultSet
	 * @param Schema &$schema
	 * @return void
	 */
	public function onBSApiStoreBaseBeforeReturnData( $store, &$resultSet, &$schema ) {
		if ( !( $store instanceof Categorylinks ) ) {
			return;
		}
		$contextTitle = null;
		$categories = [];
		foreach ( $resultSet->getRecords() as $record ) {
			if ( $contextTitle && (int)$record->get( 'page_id' ) !== $contextTitle->getArticleID() ) {
				$contextTitle = null;
				$categories = [];
			}
			if ( $record->get( 'category_is_explicit' ) === true ) {
				continue;
			}
			if ( !$contextTitle ) {
				$contextTitle = $this->titleFactory->newFromID( $record->get( 'page_id' ) );
				if ( !$contextTitle ) {
					// Invalid title, skip
					continue;
				}
				$wikiPage = $this->wikiPageFactory->newFromTitle( $contextTitle );
				$content = $wikiPage->getRevisionRecord()?->getContent( SlotRecord::MAIN );
				if ( !$content ) {
					// No content, skip
					continue;
				}
				$manipulator = $this->categoryManipulatorFactory->getManipulatorForContent( $content, $contextTitle );
				if ( !$manipulator ) {
					continue;
				}
				$categories = $manipulator->getCategories( $contextTitle );
			}
			if ( in_array( $record->get( 'category_title' ), $categories ) ) {
				$record->set( 'category_is_explicit', true );
			}

		}
	}
}
