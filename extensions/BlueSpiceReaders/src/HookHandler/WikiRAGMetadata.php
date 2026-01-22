<?php

namespace BlueSpice\Readers\HookHandler;

use MediaWiki\Extension\WikiRAG\Hook\WikiRAGMetadataHook;
use MediaWiki\Page\PageIdentity;
use MediaWiki\Revision\RevisionRecord;
use Wikimedia\Rdbms\ILoadBalancer;

class WikiRAGMetadata implements WikiRAGMetadataHook {

	/**
	 * @param ILoadBalancer $lb
	 */
	public function __construct(
		private readonly ILoadBalancer $lb
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function onWikiRAGMetadata( PageIdentity $page, RevisionRecord $revision, array &$meta ): void {
		$threeMonthsAgo = wfTimestamp( TS_MW, strtotime( '-3 months' ) );
		$db = $this->lb->getConnectionRef( DB_REPLICA );
		$row = $db->newSelectQueryBuilder()
			->select( [ 'COUNT(*) as recent_visits', 'MAX(readers_ts) as latest_visit' ] )
			->from( 'bs_readers' )
			->where( [
				'readers_page_id' => $page->getId(),
				$db->buildComparison( '>=', [
					'readers_ts' => $threeMonthsAgo
				] )
			] )
			->caller( __METHOD__ )
			->fetchRow();
		if ( $row ) {
			$meta['recent_visits'] = (int)$row->recent_visits;
			$meta['latest_visit'] = $row->latest_visit;
		}
	}
}
