<?php

namespace BlueSpice\InsertCategory\CategoryManipulator;

use BlueSpice\InsertCategory\Content\CategoryContent;
use BlueSpice\InsertCategory\ICategoryManipulator;
use MediaWiki\CommentStore\CommentStoreComment;
use MediaWiki\Message\Message;
use MediaWiki\Page\PageIdentity;
use MediaWiki\Page\WikiPageFactory;
use MediaWiki\Permissions\Authority;
use MediaWiki\Revision\RevisionLookup;
use MediaWiki\Revision\RevisionRecord;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class CategorySlotManipulator implements ICategoryManipulator, LoggerAwareInterface {

	/** @var LoggerInterface|NullLogger */
	private LoggerInterface $logger;

	/**
	 * @param WikiPageFactory $wikiPageFactory
	 * @param RevisionLookup $revisionLookup
	 */
	public function __construct(
		private readonly WikiPageFactory $wikiPageFactory,
		private readonly RevisionLookup $revisionLookup
	) {
		$this->logger = new NullLogger();
	}

	/**
	 * @param PageIdentity $pageIdentity
	 * @param RevisionRecord|null $revisionRecord
	 * @return array
	 */
	public function getCategories( PageIdentity $pageIdentity, ?RevisionRecord $revisionRecord = null ): array {
		$content = $this->getCategoryContent( $pageIdentity, $revisionRecord );
		if ( !$content ) {
			return [];
		}
		return $content->getCategories();
	}

	/**
	 * @param PageIdentity $pageIdentity
	 * @param array $categoryTitles
	 * @param Authority $actor
	 * @return bool
	 */
	public function setCategories( PageIdentity $pageIdentity, array $categoryTitles, Authority $actor ): bool {
		$content = $this->getCategoryContent( $pageIdentity );
		if ( !$content ) {
			$content = new CategoryContent();
		}
		$content = $content->setCategories( $categoryTitles );
		$wikiPage = $this->wikiPageFactory->newFromTitle( $pageIdentity );
		$updater = $wikiPage->newPageUpdater( $actor );
		$updater->setContent( 'category_storage', $content );
		$updater->saveRevision( CommentStoreComment::newUnsavedComment(
			Message::newFromKey( 'bs-insertcategory-set-categories-summary' )->text()
		), EDIT_MINOR );
		if ( !$updater->getStatus()->isOK() ) {
			$this->logger->error( 'Failed to set categories to `category_storage` slot', [
				'messages' => array_map( static function ( $specifier ) {
					return Message::newFromSpecifier( $specifier )->text();
				}, $updater->getStatus()->getMessages() ),
			] );
			return false;
		}
		return true;
	}

	/**
	 * @param PageIdentity $pageIdentity
	 * @param RevisionRecord|null $revisionRecord
	 * @return CategoryContent|null
	 */
	private function getCategoryContent( PageIdentity $pageIdentity, ?RevisionRecord $revisionRecord = null ) {
		$revisionRecord = $revisionRecord ?? $this->revisionLookup->getRevisionByTitle( $pageIdentity );
		if ( !$revisionRecord ) {
			return null;
		}
		if ( !$revisionRecord->hasSlot( 'category_storage' ) ) {
			// No category storage slot, return null
			return null;
		}
		$content = $revisionRecord->getContent( 'category_storage' );
		if ( !( $content instanceof CategoryContent ) ) {
			return null;
		}
		return $content;
	}

	public function setLogger( LoggerInterface $logger ): void {
		$this->logger = $logger;
	}
}
