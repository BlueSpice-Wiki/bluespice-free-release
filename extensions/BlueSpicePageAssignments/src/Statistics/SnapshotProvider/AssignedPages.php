<?php

namespace BlueSpice\PageAssignments\Statistics\SnapshotProvider;

use BlueSpice\ExtendedStatistics\ISnapshotProvider;
use BlueSpice\ExtendedStatistics\Snapshot;
use BlueSpice\ExtendedStatistics\SnapshotDate;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use Wikimedia\Rdbms\LoadBalancer;

class AssignedPages implements ISnapshotProvider {
	/** @var LoadBalancer */
	private $loadBalancer;

	/**
	 * @param LoadBalancer $loadBalancer
	 */
	public function __construct( LoadBalancer $loadBalancer ) {
		$this->loadBalancer = $loadBalancer;
	}

	/**
	 * @param SnapshotDate $date
	 * @return Snapshot
	 */
	public function generateSnapshot( SnapshotDate $date ): Snapshot {
		$db = $this->loadBalancer->getConnection( DB_REPLICA );

		$resCategories = $db->select(
			[ 'page', 'bs_pageassignments', 'categorylinks' ],
			[ 'COUNT(page_title) as pages', 'page_namespace', 'pa_page_id', 'cl_to' ],
			[],
			__METHOD__,
			[
				'GROUP BY' => 'page_namespace, pa_page_id, cl_to'
			],
			[
				'bs_pageassignments' => [
					"LEFT OUTER JOIN", [ 'page_id=pa_page_id' ]
				],
				'categorylinks' => [
					"LEFT OUTER JOIN", [ 'page_id=cl_from' ]
				]
			]
		);

		$resNamespaces = $db->select(
			[ 'page', 'bs_pageassignments' ],
			[ 'COUNT(page_title) as pages', 'page_namespace', 'pa_page_id' ],
			[],
			__METHOD__,
			[
				'GROUP BY' => 'page_namespace, pa_page_id'
			],
			[
				'bs_pageassignments' => [
					"LEFT OUTER JOIN", [ 'page_id=pa_page_id' ]
				]
			]
		);

		$assigned = 0;
		$unassigned = 0;
		$namespaces = [];
		$categories = [];
		$namespaceInfo = MediaWikiServices::getInstance()->getNamespaceInfo();
		foreach ( $resCategories as $row ) {
			$pageCount = (int)$row->pages;

			if ( (int)$row->page_namespace === 0 ) {
				$namespace = '-';
			} else {
				$namespace = $namespaceInfo->getCanonicalName( $row->page_namespace );
			}
			$category = $row->cl_to;
			$hasAssignment = (bool)$row->pa_page_id;

			$hasAssignment ? $assigned++ : $unassigned++;
			if ( !isset( $namespaces[$namespace] ) ) {
				$namespaces[$namespace] = [ 'assigned' => 0, 'unassigned' => 0 ];
			}

			$hasAssignment ?
				$namespaces[$namespace]['assigned'] += $pageCount :
				$namespaces[$namespace]['unassigned'] += $pageCount;
			if ( $category ) {
				if ( !isset( $categories[$category] ) ) {
					$categories[$category] = [ 'assigned' => 0, 'unassigned' => 0 ];
				}
				$hasAssignment ?
					$categories[$category]['assigned'] += $pageCount :
					$categories[$category]['unassigned'] += $pageCount;
			}
		}

		foreach ( $resNamespaces as $row ) {
			$pageCount = (int)$row->pages;

			if ( (int)$row->page_namespace === 0 ) {
				$namespace = '-';
			} else {
				$namespace = $namespaceInfo->getCanonicalName( $row->page_namespace );
			}
			$hasAssignment = (bool)$row->pa_page_id;

			$hasAssignment ? $assigned++ : $unassigned++;
			if ( !isset( $namespaces[$namespace] ) ) {
				$namespaces[$namespace] = [ 'assigned' => 0, 'unassigned' => 0 ];
			}

			$hasAssignment ?
				$namespaces[$namespace]['assigned'] = $pageCount :
				$namespaces[$namespace]['unassigned'] = $pageCount;

		}

		return new Snapshot( $date, $this->getType(), [
			'assigned' => $assigned,
			'unassigned' => $unassigned,
			'namespace' => $namespaces,
			'categories' => $categories,
		] );
	}

	/**
	 * @inheritDoc
	 */
	public function aggregate(
		array $snapshots, $interval = Snapshot::INTERVAL_DAY, $date = null
	): Snapshot {
		$lastSnapshot = array_pop( $snapshots );

		return new Snapshot(
			$date ?? new SnapshotDate(), $this->getType(), $lastSnapshot->getData(), $interval
		);
	}

	/**
	 * @inheritDoc
	 */
	public function getType() {
		return 'pa-assignedpages';
	}

	/**
	 * @param Snapshot $snapshot
	 * @return array
	 */
	public function getSecondaryData( Snapshot $snapshot ) {
		$db = $this->loadBalancer->getConnection( DB_REPLICA );

		$res = $db->select(
			[ 'page', 'bs_pageassignments', 'categorylinks' ],
			[ 'page_id', 'page_title', 'page_namespace', 'pa_page_id', 'GROUP_CONCAT( cl_to ) as cats' ],
			[],
			__METHOD__,
			[
				'GROUP BY' => 'page_id'
			],
			[
				'bs_pageassignments' => [
					"LEFT OUTER JOIN", [ 'page_id=pa_page_id' ]
				],
				'categorylinks' => [
					"LEFT OUTER JOIN", [ 'page_id=cl_from' ]
				]
			]
		);

		$data = [];
		foreach ( $res as $row ) {
			$title = Title::newFromRow( $row );
			$data[$title->getPrefixedDBkey()] = [
				'id' => (int)$row->page_id,
				'n' => (int)$row->page_namespace,
				'c' => is_string( $row->cats ) ? explode( ',', $row->cats ) : [],
				'a' => (bool)$row->pa_page_id,
			];
		}

		return $data;
	}
}
