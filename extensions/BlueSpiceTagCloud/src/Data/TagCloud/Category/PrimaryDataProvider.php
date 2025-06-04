<?php

namespace BlueSpice\TagCloud\Data\TagCloud\Category;

use BlueSpice\TagCloud\Context;
use BlueSpice\TagCloud\Data\TagCloud\Record;
use BlueSpice\TagCloud\Data\TagCloud\Schema;
use MediaWiki\Category\TrackingCategories;
use MediaWiki\Title\Title;
use MWStake\MediaWiki\Component\DataStore\Filter;
use MWStake\MediaWiki\Component\DataStore\Filter\ListValue;
use MWStake\MediaWiki\Component\DataStore\Filter\Numeric;
use MWStake\MediaWiki\Component\DataStore\Filter\StringValue;
use MWStake\MediaWiki\Component\DataStore\FilterFinder;
use MWStake\MediaWiki\Component\DataStore\IPrimaryDataProvider;
use MWStake\MediaWiki\Component\DataStore\ReaderParams;

class PrimaryDataProvider implements IPrimaryDataProvider {

	/**
	 *
	 * @var Record[]
	 */
	protected $data = [];

	/**
	 *
	 * @var \Wikimedia\Rdbms\IDatabase
	 */
	protected $db = null;

	/**
	 *
	 * @var Context
	 */
	protected $context = null;

	/**
	 * @var TrackingCategories
	 */
	private $trackingCategories = null;

	/**
	 *
	 * @param \Wikimedia\Rdbms\IDatabase $db
	 * @param Context $context
	 * @param TrackingCategories $trackingCategories
	 */
	public function __construct( $db, Context $context, TrackingCategories $trackingCategories ) {
		$this->db = $db;
		$this->context = $context;
		$this->trackingCategories = $trackingCategories;
	}

	/**
	 *
	 * @param ReaderParams $params
	 * @return array
	 */
	public function makeData( $params ) {
		$this->data = [];
		$trackingCategoriesTitles = [];
		$categoryList = $this->trackingCategories->getTrackingCategories();
		foreach ( $categoryList as $key => $config ) {
			foreach ( $config[ 'cats' ] as $title ) {
				$trackingCategoriesTitles[] = $title->getDBKey();
			}
		}
		$typeCategoryExclude = $this->context->getConfig()->get( 'TagCloudTypeCategoryExclude' );
		$typeCategoryExclude = array_map( static function ( $value ) {
			return str_replace( ' ', '_', $value );
		}, $typeCategoryExclude );
		$res = $this->db->select(
			'categorylinks',
			[ Record::NAME => 'cl_to', Record::COUNT => 'COUNT(cl_to)' ],
			$this->makePreFilterConds( $params ),
			__METHOD__,
			$this->makePreOptionConds( $params )
		);
		foreach ( $res as $row ) {
			if ( count( $this->data ) >= $params->getLimit() ) {
				break;
			}
			if ( in_array( $row->{Record::NAME}, $trackingCategoriesTitles ) ) {
				continue;
			}
			if ( in_array( $row->{Record::NAME}, $typeCategoryExclude ) ) {
				continue;
			}
			$this->appendRowToData( $row );
		}
		return $this->data;
	}

	/**
	 *
	 * @param ReaderParams $params
	 * @return array
	 */
	protected function makePreFilterConds( $params ) {
		$conds = [];
		$schema = new Schema();
		$fields = array_values( $schema->getFilterableFields() );
		$filterFinder = new FilterFinder( $params->getFilter() );
		foreach ( $fields as $fieldName ) {
			$filter = $filterFinder->findByField( $fieldName );
			if ( !$filter instanceof Filter ) {
				continue;
			}
			if ( $filter instanceof ListValue ) {
				$values = implode( "','", $filter->getValue() );
				$name = $this->aliasToFieldName( $fieldName );
				if ( $filter->getComparison() === ListValue::COMPARISON_CONTAINS ) {
					$conds[$name] = $fieldName;
					$filter->setApplied();
					continue;
				}
				if ( $filter->getComparison() === ListValue::COMPARISON_NOT_CONTAINS ) {
					$conds[] = "$name NOT IN ('$values')";
					$filter->setApplied();
					continue;
				}
			}
			switch ( $filter->getComparison() ) {
				case Numeric::COMPARISON_GREATER_THAN:
					$conds[] = "$fieldName > {$filter->getValue()}";
					break;
				case Numeric::COMPARISON_LOWER_THAN:
					$conds[] = "$fieldName < {$filter->getValue()}";
					break;
				case StringValue::COMPARISON_CONTAINS:
					$conds[] = $this->db->buildLike(
						$this->db->anyString(),
						$fieldName,
						$this->db->anyString()
					);
					break;
				case StringValue::COMPARISON_NOT_EQUALS:
				case Numeric::COMPARISON_NOT_EQUALS:
					$conds[] = "$fieldName != {$filter->getValue()}";
					break;
				case StringValue::COMPARISON_EQUALS:
				case Numeric::COMPARISON_EQUALS:
				default:
					$conds[$fieldName] = $filter->getValue();
			}
			$filter->setApplied();
		}
		return $conds;
	}

	/**
	 *
	 * @param ReaderParams $params
	 * @return array
	 */
	protected function makePreOptionConds( $params ) {
		$conds = [
			'GROUP BY' => 'cl_to',
			// 'LIMIT' => $params->getLimit(),
			'ORDER BY' => 'COUNT(cl_to) DESC'
		];

		return $conds;
	}

	/**
	 *
	 * @param \stdClass $row
	 */
	protected function appendRowToData( $row ) {
		$title = Title::newFromText( $row->{Record::NAME}, NS_CATEGORY );
		$pm = \MediaWiki\MediaWikiServices::getInstance()->getPermissionManager();
		if ( !$title || !$pm->userCan( 'read', $this->context->getUser(), $title ) ) {
			return;
		}
		$this->data[] = new Record( (object)[
			Record::NAME => $title->getText(),
			Record::COUNT => (int)$row->{Record::COUNT},
			Record::LINK => '',
		] );
	}

	/**
	 * cause of mysql alias resons -.-
	 * @param string $alias
	 * @return string
	 */
	protected function aliasToFieldName( $alias ) {
		switch ( $alias ) {
			case Record::NAME:
				$alias = 'cl_to';
				break;
			case Record::COUNT:
				$alias = 'count(cl_to)';
				break;
			default:
		}
		return $alias;
	}
}
