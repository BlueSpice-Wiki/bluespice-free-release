<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs;

use ManualLogEntry;
use MediaWiki\Collation\CollationFactory;
use MediaWiki\Hook\AfterImportPageHook;
use MediaWiki\Hook\PageMoveCompleteHook;
use MediaWiki\Language\Language;
use MediaWiki\Page\Hook\PageDeleteCompleteHook;
use MediaWiki\Page\Hook\PageUndeleteCompleteHook;
use MediaWiki\Page\PageIdentity;
use MediaWiki\Page\PageProps;
use MediaWiki\Page\ProperPageIdentity;
use MediaWiki\Permissions\Authority;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Storage\Hook\PageSaveCompleteHook;
use MediaWiki\Title\Title;
use Wikimedia\Rdbms\DBConnRef;
use Wikimedia\Rdbms\ILoadBalancer;

class TitleIndexUpdater implements
	PageSaveCompleteHook,
	PageMoveCompleteHook,
	PageDeleteCompleteHook,
	PageUndeleteCompleteHook,
	AfterImportPageHook
{
	use ContentLanguageCollationTrait;

	/**
	 * @var ILoadBalancer
	 */
	private $lb;

	/**
	 * @var PageProps
	 */
	private $pageProps;

	/**
	 * @var CollationFactory
	 */
	private $collationFactory;

	/**
	 * @var Language
	 */
	private $contentLanguage;

	/**
	 * @param ILoadBalancer $lb
	 * @param PageProps $pageProps
	 * @param CollationFactory $collationFactory
	 * @param Language $contentLanguage
	 */
	public function __construct(
		ILoadBalancer $lb, PageProps $pageProps,
		CollationFactory $collationFactory, Language $contentLanguage
	) {
		$this->lb = $lb;
		$this->pageProps = $pageProps;
		$this->collationFactory = $collationFactory;
		$this->contentLanguage = $contentLanguage;
	}

	/**
	 * @inheritDoc
	 */
	public function onPageSaveComplete(
		$wikiPage, $user, $summary, $flags, $revisionRecord, $editResult
	) {
		$this->insert( $wikiPage->getTitle() );
	}

	/**
	 * @inheritDoc
	 */
	public function onPageMoveComplete( $old, $new, $user, $pageid, $redirid, $reason, $revision ) {
		$this->delete( $old->getNamespace(), $old->getDBkey() );
		$this->insert( $new );
		if ( $redirid ) {
			$this->insert( $old );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function onPageDeleteComplete(
		ProperPageIdentity $page, Authority $deleter, string $reason, int $pageID,
		RevisionRecord $deletedRev, ManualLogEntry $logEntry, int $archivedRevisionCount
	) {
		$this->delete( $page->getNamespace(), $page->getDBkey() );
	}

	/**
	 * @inheritDoc
	 */
	public function onPageUndeleteComplete(
		ProperPageIdentity $page,
		Authority $restorer,
		string $reason,
		RevisionRecord $restoredRev,
		ManualLogEntry $logEntry,
		int $restoredRevisionCount,
		bool $created,
		array $restoredPageIds
	): void {
		$page = Title::newFromPageIdentity( $page );
		$this->insert( $page, $page->getId() );
	}

	/**
	 * @inheritDoc
	 */
	public function onAfterImportPage( $title, $foreignTitle, $revCount, $sRevCount, $pageInfo ) {
		$this->insert( $title );
	}

	/**
	 * @param Title $page
	 * @param int|null $forceId (optional)
	 *
	 * @return bool
	 */
	private function insert( Title $page, $forceId = null ) {
		/** @var DBConnRef $db */
		$db = $this->lb->getConnection( DB_PRIMARY );
		if ( !$db->tableExists( 'mws_title_index', __METHOD__ ) ) {
			return false;
		}
		if ( !$page->exists() ) {
			return false;
		}

		$leaf = '';
		if ( strpos( $page->getDBkey(), '/' ) !== false ) {
			$bits = explode( '/', $page->getDBkey() );
			$leaf = array_pop( $bits );
		}

		$data = [
			'mti_page_id' => $forceId ?? $page->getId(),
			'mti_namespace' => $page->getNamespace(),
			'mti_title' => mb_strtolower( str_replace( '_', ' ', $page->getDBkey() ) ),
			'mti_displaytitle' => $this->getDisplayTitle( $page ),
			'mti_leaf_title' => mb_strtolower( str_replace( '_', ' ', $leaf ) ),
			'mti_first_letter' => $this->getFirstLetter( $page->getDBkey() )
		];

		return $db->upsert(
			'mws_title_index',
			$data,
			[ [ 'mti_page_id' ] ],
			$data,
			__METHOD__
		);
	}

	/**
	 * @param int $namespace
	 * @param string $title
	 *
	 * @return bool
	 */
	private function delete( int $namespace, string $title ) {
		/** @var DBConnRef $db */
		$db = $this->lb->getConnection( DB_PRIMARY );
		if ( !$db->tableExists( 'mws_title_index', __METHOD__ ) ) {
			return false;
		}
		return $db->delete(
			'mws_title_index',
			[
				'mti_namespace' => $namespace,
				'mti_title' => mb_strtolower( str_replace( '_', ' ', $title ) ),
			],
			__METHOD__
		);
	}

	/**
	 * @param PageIdentity $page
	 *
	 * @return string
	 */
	private function getDisplayTitle( PageIdentity $page ): string {
		$display = $this->pageProps->getProperties( $page, 'displaytitle' );
		if ( isset( $display[$page->getId()] ) ) {
			return mb_strtolower( str_replace( '_', ' ', $display[$page->getId()] ) );
		}
		return '';
	}

	/**
	 * Compute the first-letter bucket for a given page dbkey.
	 * Uses root page name (before first '/') for subpages.
	 * Digits are grouped as "0-9", special chars as "#".
	 *
	 * @param string $dbkey
	 * @return string
	 */
	private function getFirstLetter( string $dbkey ): string {
		$title = str_replace( '_', ' ', explode( '/', $dbkey )[0] );
		$collation = $this->resolveCollation( $this->collationFactory, $this->contentLanguage );
		$letter = $collation->getFirstLetter( $title );

		if ( $letter === '' ) {
			return '#';
		}
		if ( ctype_digit( $letter ) ) {
			return '0-9';
		}
		return $letter;
	}
}
