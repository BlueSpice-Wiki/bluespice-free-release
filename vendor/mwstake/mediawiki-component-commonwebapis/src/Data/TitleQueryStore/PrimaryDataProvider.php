<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs\Data\TitleQueryStore;

use MediaWiki\Language\Language;
use MediaWiki\Title\NamespaceInfo;
use MWStake\MediaWiki\Component\DataStore\Filter;
use MWStake\MediaWiki\Component\DataStore\PrimaryDatabaseDataProvider;
use MWStake\MediaWiki\Component\DataStore\ReaderParams;
use MWStake\MediaWiki\Component\DataStore\Schema;
use Wikimedia\Rdbms\IDatabase;

class PrimaryDataProvider extends PrimaryDatabaseDataProvider {

	/** @var Language */
	protected $language;

	/** @var array */
	protected $contentNamespaces;

	/** @var NamespaceInfo */
	protected $nsInfo;

	/**
	 * @param IDatabase $db
	 * @param Schema $schema
	 * @param Language $language
	 * @param NamespaceInfo $nsInfo
	 */
	public function __construct(
		IDatabase $db, Schema $schema, Language $language, NamespaceInfo $nsInfo
	) {
		parent::__construct( $db, $schema );
		$this->language = $language;
		$this->nsInfo = $nsInfo;
		$this->contentNamespaces = $nsInfo->getContentNamespaces();
	}

	/**
	 * @param ReaderParams $params
	 *
	 * @return array
	 */
	protected function makePreFilterConds( ReaderParams $params ) {
		$filters = $params->getFilter();
		$conds = parent::makePreFilterConds( $params );
		$query = $params->getQuery();
		$nsFilter = [];
		foreach ( $filters as $filter ) {
			if (
				in_array( $filter->getField(), [
					TitleRecord::PAGE_DBKEY, TitleRecord::PAGE_DISPLAY_TITLE, TitleRecord::PAGE_TITLE
				] )
			) {
				$filter->setApplied( true );
				if ( !$query && $filter->getComparison() === Filter::COMPARISON_CONTAINS ) {
					$query = $filter->getValue();
				}
			}

			if ( $filter->getField() === TitleRecord::PAGE_NAMESPACE ) {
				if ( !( $filter instanceof Filter\ListValue ) ) {
					$filter = new Filter\StringValue( [
						Filter::KEY_FIELD => TitleRecord::PAGE_NAMESPACE,
						Filter::KEY_VALUE => [ $filter->getValue() ],
						Filter::KEY_COMPARISON => 'in'
					] );
				}
				$nsFilter = array_merge( $nsFilter, $filter->getValue() );
				$filter->setApplied( true );
			}

			if ( $filter->getField() === TitleRecord::IS_CONTENT_PAGE ) {
				if ( $filter->getValue() ) {
					$nsFilter = array_merge( $nsFilter, $this->contentNamespaces );
				} else {
					$conds[] = 'mti_namespace NOT IN (' . $this->db->makeList( $this->contentNamespaces ) . ')';
				}
			}
			if ( $filter->getField() === TitleRecord::PAGE_CONTENT_MODEL ) {
				if ( !( $filter instanceof Filter\ListValue ) ) {
					$filter = new Filter\StringValue( [
						Filter::KEY_FIELD => TitleRecord::PAGE_CONTENT_MODEL,
						Filter::KEY_VALUE => [ $filter->getValue() ],
						Filter::KEY_COMPARISON => 'in'
					] );
				}
				$filter->setApplied( true );
				$conds[] = 'page_content_model IN (' . $this->db->makeList( $filter->getValue() ) . ')';
			}
		}

		if ( $query !== '' ) {
			$colonPos = mb_strpos( $query, ':' );
			if ( $colonPos !== false ) {
				$queryParts = explode( ':', $query, 2 );
				$nsText = $queryParts[0] ?? '';
				$queryText = $query;
				$nsIndex = $this->language->getLocalNsIndex( $nsText );
				if ( $nsIndex !== false ) {
					if ( empty( $nsFilter ) || in_array( $nsIndex, $nsFilter ) ) {
						$nsFilter = [ $nsIndex ];
						$queryText = $queryParts[1] ?? $queryParts[0];
					}
				}
				$conds[] = $this->processQuery( $queryText );
			} else {
				$conds[] = $this->processQuery( $query );
			}
		}

		if ( !empty( $nsFilter ) ) {
			$conds[] = 'mti_namespace IN (' . $this->db->makeList( $nsFilter ) . ')';
		}

		return $conds;
	}

	/**
	 * @param string $query
	 * @return string
	 */
	protected function processQuery( string $query ) {
		$query = mb_strtolower( str_replace( '_', ' ', $query ) );
		$titleQuery = 'mti_title ' . $this->db->buildLike(
			$this->db->anyString(), $query, $this->db->anyString()
		);
		$displayTitleQuery = 'mti_displaytitle ' . $this->db->buildLike(
			$this->db->anyString(), $query, $this->db->anyString()
		);
		return "($titleQuery OR $displayTitleQuery)";
	}

	/**
	 * Replace filters for page title with normalized field
	 *
	 * @param array &$conds
	 * @param Filter $filter
	 *
	 * @return void
	 */
	protected function appendPreFilterCond( &$conds, Filter $filter ) {
		if ( in_array( $filter->getField(), [ TitleRecord::PAGE_DBKEY, TitleRecord::PAGE_TITLE ] ) ) {
			$filter = new Filter\StringValue( [
				Filter::KEY_FIELD => 'mti_title',
				Filter::KEY_VALUE => mb_strtolower( str_replace( '_', ' ', $filter->getValue() ) ),
				Filter::KEY_COMPARISON => $filter->getComparison()
			] );
		}
		parent::appendPreFilterCond( $conds, $filter );
	}

	/**
	 * @inheritDoc
	 */
	protected function getFields() {
		return [ 'mti_page_id', 'mti_title', 'page_namespace', 'page_title', 'page_content_model', 'page_lang' ];
	}

	/**
	 * @inheritDoc
	 */
	protected function skipPreFilter( Filter $filter ) {
		return in_array( $filter->getField(), [
			TitleRecord::PAGE_NAMESPACE, TitleRecord::PAGE_NAMESPACE_TEXT,
			TitleRecord::IS_CONTENT_PAGE
		] );
	}

	/**
	 * @param \stdClass $row
	 *
	 * @return void
	 */
	protected function appendRowToData( \stdClass $row ) {
		$this->data[] = new TitleRecord( (object)[
			TitleRecord::PAGE_ID => (int)$row->mti_page_id,
			TitleRecord::PAGE_NAMESPACE => (int)$row->page_namespace,
			TitleRecord::PAGE_DBKEY => $row->page_title,
			TitleRecord::PAGE_CONTENT_MODEL => $row->page_content_model,
			// B/C
			'content_model' => $row->page_content_model,
			TitleRecord::IS_CONTENT_PAGE => in_array( $row->page_namespace, $this->contentNamespaces ),
			TitleRecord::PAGE_EXISTS => true,
		] );
	}

	/**
	 * @inheritDoc
	 */
	protected function getJoinConds( ReaderParams $params ) {
		return [
			'page' => [
				'INNER JOIN', [ 'mti_page_id = page_id' ]
			]
		];
	}

	/**
	 * @return string[]
	 */
	protected function getTableNames() {
		return [ 'mws_title_index', 'page' ];
	}
}
