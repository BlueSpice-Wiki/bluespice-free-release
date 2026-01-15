<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs\Data\FileQueryStore;

use MWStake\MediaWiki\Component\CommonWebAPIs\Data\TitleQueryStore\PrimaryDataProvider
	as TitlePrimaryDataProvider;
use MWStake\MediaWiki\Component\CommonWebAPIs\Data\TitleQueryStore\TitleRecord;
use MWStake\MediaWiki\Component\DataStore\Filter;
use MWStake\MediaWiki\Component\DataStore\ReaderParams;

class PrimaryDataProvider extends TitlePrimaryDataProvider {

	/** @var array */
	private $dbFieldMapping = [
		'timestamp' => 'img_timestamp',
		'title' => 'mti_title',
		'file_size' => 'img_size',
		'file_extension' => 'img_minor_mime',
	];

	/**
	 * @param ReaderParams $params
	 *
	 * @return array
	 */
	protected function makePreFilterConds( ReaderParams $params ) {
		$filters = $params->getFilter();
		$conds = parent::makePreFilterConds( $params );
		$conds[] = 'mti_namespace = ' . NS_FILE;
		foreach ( $filters as $filter ) {
			if ( $filter->getField() === 'namespace_text' ) {
				$filterValue = $filter->getValue();
				if ( !is_array( $filterValue ) ) {
					$filterValue = [ $filterValue ];
				}
				$nsConds = [];
				foreach ( $filterValue as $value ) {
					// Special case for NSFR:
					// Filtering by namespace is a bit tricky, as NSFR stores namespaces as part of title
					$value = mb_strtolower( str_replace( '_', ' ', $value ) );
					$nsConds[] = 'mti_title LIKE "' . $value . ':%"';
				}
				$conds[] = implode( ' OR ', $nsConds );
				$filter->setApplied( true );
			}
			if ( $filter->getField() === FileRecord::FILE_EXTENSION ) {
				$extensions = $filter->getValue();
				if ( !is_array( $extensions ) ) {
					$extensions = [ $extensions ];
				}
				$conds[] = $this->db->makeList( array_map( static function ( $extension ) {
					return 'mti_title LIKE "%.' . trim( strtolower( $extension ) ) . '"';
				}, $extensions ), LIST_OR );
				$filter->setApplied( true );
			}
			if ( $filter->getField() === FileRecord::FILE_SIZE ) {
				$filterValue = $filter->getValue();
				if ( !is_array( $filterValue ) ) {
					$filterValue = [ $filterValue ];
				}
				$filter->setApplied( true );
			}
		}
		return $conds;
	}

	/**
	 * @param array &$conds
	 * @param Filter $filter
	 * @return void
	 */
	protected function appendPreFilterCond( &$conds, Filter $filter ) {
		if ( $filter->getField() === 'timestamp' ) {
			$time = new \DateTime( $filter->getValue() );
			if ( $filter->getComparison() === Filter::COMPARISON_EQUALS ) {
				try {
					$threshold = clone $time;
					$threshold->setTime( 0, 0, 0 );
					$filter = new Filter\StringValue( [
						'field' => 'img_timestamp',
						'value' => $threshold->format( 'YmdHis' ),
						'comparison' => Filter\NumericValue::COMPARISON_GREATER_THAN
					] );
					parent::appendPreFilterCond( $conds, $filter );

					$ceiling = clone $threshold;
					$ceiling->add( new \DateInterval( 'P1D' ) );
					$filter = new Filter\StringValue( [
						'field' => 'img_timestamp',
						'value' => $ceiling->format( 'YmdHis' ),
						'comparison' => Filter\NumericValue::COMPARISON_LOWER_THAN
					] );
					parent::appendPreFilterCond( $conds, $filter );
				} catch ( \Exception $e ) {
					return;
				}
				return;
			}

			$filter = new Filter\StringValue( [
				'field' => 'img_timestamp',
				'value' => $time->format( 'YmdHis' ),
				'comparison' => $filter->getComparison()
			] );
		}
		if ( $filter->getField() === 'author' ) {
			$users = $filter->getValue();
			if ( !is_array( $users ) ) {
				$users = [ $users ];
			}
			$filter = new Filter\ListValue( [
				'field' => 'actor_name',
				'value' => $users,
				'comparison' => Filter\ListValue::COMPARISON_IN
			] );
		}
		if ( $filter->getField() === FileRecord::FILE_SIZE ) {
			$filterValue = $filter->getValue();
			$filter = new Filter\NumericValue( [
				Filter::KEY_FIELD => 'img_size',
				Filter::KEY_VALUE => $filterValue,
				Filter::KEY_COMPARISON => $filter->getComparison()
			] );
		}
		parent::appendPreFilterCond( $conds, $filter );
	}

	/**
	 * @inheritDoc
	 */
	protected function getFields() {
		return array_merge(
			parent::getFields(), [
				'img_actor', 'img_major_mime', 'img_minor_mime', 'actor_name',
				'comment_text', "GROUP_CONCAT( cl_to SEPARATOR '|') categories",
				'img_timestamp', 'img_size'
			] );
	}

	/**
	 * @inheritDoc
	 */
	protected function skipPreFilter( Filter $filter ) {
		return in_array( $filter->getField(), [
			TitleRecord::PAGE_DBKEY, TitleRecord::PAGE_TITLE,
			TitleRecord::PAGE_NAMESPACE, TitleRecord::PAGE_NAMESPACE_TEXT,
			TitleRecord::IS_CONTENT_PAGE, FileRecord::FILE_EXTENSION
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
			TitleRecord::PAGE_NAMESPACE => NS_FILE,
			TitleRecord::PAGE_TITLE => $row->page_title,
			TitleRecord::PAGE_DBKEY => $row->page_title,
			TitleRecord::PAGE_CONTENT_MODEL => $row->page_content_model,
			TitleRecord::IS_CONTENT_PAGE => in_array( $row->page_namespace, $this->contentNamespaces ),
			FileRecord::FILE_TIMESTAMP => $row->img_timestamp,
			FileRecord::FILE_EXTENSION => $this->getExtension( $row->page_title ),
			FileRecord::FILE_SIZE => $row->img_size,
			FileRecord::MIME_MAJOR => $row->img_major_mime,
			FileRecord::MIME_MINOR => $row->img_minor_mime,
			FileRecord::FILE_AUTHOR_ID => $row->img_actor,
			FileRecord::FILE_AUTHOR_NAME => $row->actor_name ?? '',
			FileRecord::FILE_COMMENT => $row->comment_text,
			FileRecord::FILE_CATEGORIES => $row->categories
		] );
	}

	/**
	 * @inheritDoc
	 */
	protected function getJoinConds( ReaderParams $params ) {
		return array_merge( parent::getJoinConds( $params ), [
			'page' => [
				'INNER JOIN', [ 'mti_page_id = page_id' ]
			],
			'image' => [
				'INNER JOIN', [ 'page_title = img_name' ]
			],
			'comment' => [
				'INNER JOIN', [ 'img_description_id = comment_id' ]
			],
			'categorylinks' => [
				'LEFT JOIN', [ 'page_id = cl_from' ]
			],
			'actor' => [
				'LEFT JOIN', [ 'img_actor = actor_id' ]
			],
		] );
	}

	/**
	 * @return string[]
	 */
	protected function getTableNames() {
		return array_merge( parent::getTableNames(), [
			'image', 'comment', 'categorylinks', 'actor'
		] );
	}

	/**
	 * @param ReaderParams $params
	 * @return array|string[]
	 */
	protected function makePreOptionConds( ReaderParams $params ) {
		$conds = $this->getDefaultOptions();

		$fields = array_values( $this->schema->getSortableFields() );

		foreach ( $params->getSort() as $sort ) {
			if ( !in_array( $sort->getProperty(), $fields ) ) {
				continue;
			}
			if ( !isset( $conds['ORDER BY'] ) ) {
				$conds['ORDER BY'] = "";
			} else {
				$conds['ORDER BY'] .= ",";
			}
			$property = $sort->getProperty();
			if ( isset( $this->dbFieldMapping[$property] ) ) {
				$property = $this->dbFieldMapping[$property];
			}
			$conds['ORDER BY'] .= "$property {$sort->getDirection()}";
		}
		return $conds;
	}

	/**
	 *
	 * @return array
	 */
	protected function getDefaultOptions() {
		return [ 'GROUP BY' => 'page_id' ];
	}

	/**
	 * @param string $title
	 *
	 * @return string
	 */
	private function getExtension( string $title ): string {
		$bits = explode( '.', $title );
		return strtolower( array_pop( $bits ) );
	}
}
