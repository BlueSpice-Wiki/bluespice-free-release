<?php

namespace BlueSpice\InsertCategory\CategoryManipulator;

use BlueSpice\InsertCategory\ICategoryManipulator;
use MediaWiki\CommentStore\CommentStoreComment;
use MediaWiki\Content\WikitextContent;
use MediaWiki\Message\Message;
use MediaWiki\Page\PageIdentity;
use MediaWiki\Page\WikiPageFactory;
use MediaWiki\Permissions\Authority;
use MediaWiki\Revision\RevisionLookup;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Title\TitleFactory;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class WikitextCategoryManipulator implements ICategoryManipulator, LoggerAwareInterface {

	/** @var LoggerInterface|NullLogger */
	private LoggerInterface $logger;

	/**
	 * @param WikiPageFactory $wikiPageFactory
	 * @param RevisionLookup $revisionLookup
	 * @param TitleFactory $titleFactory
	 */
	public function __construct(
		private readonly WikiPageFactory $wikiPageFactory,
		private readonly RevisionLookup $revisionLookup,
		private readonly TitleFactory $titleFactory
	) {
		$this->logger = new NullLogger();
	}

	/**
	 * @param PageIdentity $pageIdentity
	 * @param RevisionRecord|null $revisionRecord
	 * @return array
	 */
	public function getCategories( PageIdentity $pageIdentity, ?RevisionRecord $revisionRecord = null ): array {
		$wikitext = $this->getContent( $pageIdentity, $revisionRecord )?->getText();
		if ( !$wikitext ) {
			return [];
		}
		return $this->getCategoriesFromWikitext( $wikitext );
	}

	/**
	 * @param PageIdentity $pageIdentity
	 * @param array $categoryTitles
	 * @param Authority $actor
	 * @return bool
	 */
	public function setCategories( PageIdentity $pageIdentity, array $categoryTitles, Authority $actor ): bool {
		$content = $this->getContent( $pageIdentity );
		$wikitext = $content?->getText();
		if ( !$wikitext ) {
			$this->logger->warning( 'No wikitext found for page', [ 'page' => $pageIdentity->getFullText() ] );
			return false;
		}

		$current = $this->getCategoriesFromWikitext( $wikitext );
		$toRemove = array_merge( [], $current );
		$toAdd = [];
		foreach ( $categoryTitles as $categoryTitle ) {
			if ( isset( $current[$categoryTitle->getText()] ) ) {
				// Category exists already, nothing to do
				unset( $toRemove[$categoryTitle->getText()] );
				continue;
			}
			$toAdd[] = $categoryTitle;
		}
		if ( empty( $toRemove ) && empty( $toAdd ) ) {
			$this->logger->info(
				'No changes to categories, skipping save',
				[
					'namespace' => $pageIdentity->getNamespace(),
					'dbkey' => $pageIdentity->getDBkey(),
				]
			);
			return true;
		}
		$wikitext = $this->modifyWikitext( $toAdd, $toRemove, $wikitext );
		$wikiPage = $this->wikiPageFactory->newFromTitle( $pageIdentity );
		$newContent = $content->getContentHandler()->unserializeContent( $wikitext );
		$updater = $wikiPage->newPageUpdater( $actor );
		$updater->setContent( SlotRecord::MAIN, $newContent );
		$updater->saveRevision(
			CommentStoreComment::newUnsavedComment(
				Message::newFromKey( 'bs-insertcategory-set-categories-summary' )->text()
			),
			EDIT_MINOR
		);
		if ( !$updater->getStatus()->isOK() ) {
			$this->logger->error( 'Failed to set categories in wikitext', [
				'messages' => array_map( static function ( $specifier ) {
					return Message::newFromSpecifier( $specifier )->text();
				}, $updater->getStatus()->getMessages() ),
			] );
			return false;
		}
		return true;
	}

	/**
	 * @param array $toAdd
	 * @param array $toRemove
	 * @param string $wikitext
	 * @return string
	 */
	private function modifyWikitext( array $toAdd, array $toRemove, string $wikitext ): string {
		$newWikitext = trim( $wikitext );
		foreach ( $toRemove as $categoryWikitext ) {
			foreach ( $categoryWikitext as $categoryOccurrence ) {
				// Try to replace $categoryWikitext\n or $categoryWikitext at the end of the string
				$newWikitext = str_replace( $categoryOccurrence . "\n", '', $newWikitext );
				$newWikitext = str_replace( $categoryOccurrence, '', $newWikitext );
			}
		}
		foreach ( $toAdd as $categoryTitle ) {
			$newWikitext .= "\n[[" . $categoryTitle->getPrefixedText() . "]]";
		}
		return $newWikitext;
	}

	/**
	 * @param string $wikitext
	 * @return array
	 */
	private function getCategoriesFromWikitext( string $wikitext ): array {
		return $this->parseCategories( $wikitext );
	}

	/**
	 * @param PageIdentity $pageIdentity
	 * @param RevisionRecord|null $revisionRecord
	 * @return WikitextContent|null
	 */
	private function getContent(
		PageIdentity $pageIdentity, ?RevisionRecord $revisionRecord = null
	): ?WikitextContent {
		$revisionRecord = $revisionRecord ?? $this->revisionLookup->getRevisionByTitle( $pageIdentity );
		if ( !$revisionRecord ) {
			return null;
		}
		$content = $revisionRecord->getContent( SlotRecord::MAIN );
		if ( !( $content instanceof WikitextContent ) ) {
			return null;
		}
		return $content;
	}

	/**
	 * @param string $wikitext
	 * @return array
	 */
	private function parseCategories( string $wikitext ): array {
		$categories = [];
		$pattern = '#\[\[([ :])?(.*?)([\|].*?\]\]|\]\])#si';
		$matches = [];
		preg_match_all( $pattern, $wikitext, $matches );
		foreach ( $matches[2] as $index => $categoryName ) {
			$title = $this->titleFactory->newFromText( $categoryName );
			if ( $title->getNamespace() !== NS_CATEGORY ) {
				continue;
			}
			if ( !isset( $categories[$title->getText()] ) ) {
				$categories[$title->getText()] = [];
			}
			$categories[$title->getText()][] = $matches[0][$index];
		}
		return $categories;
	}

	/**
	 * @param LoggerInterface $logger
	 * @return void
	 */
	public function setLogger( LoggerInterface $logger ): void {
		$this->logger = $logger;
	}
}
