<?php

use BlueSpice\ExtendedStatistics\ISnapshotProvider;
use BlueSpice\ExtendedStatistics\PageHitsSnapshot;
use BlueSpice\ExtendedStatistics\Snapshot;
use BlueSpice\ExtendedStatistics\SnapshotDate;
use BlueSpice\ExtendedStatistics\SnapshotDateRange;
use BlueSpice\ExtendedStatistics\SnapshotFactory;
use MediaWiki\Maintenance\Maintenance;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\NamespaceInfo;
use MediaWiki\Title\Title;
use Wikimedia\Rdbms\IDatabase;

require_once dirname( dirname( dirname( __DIR__ ) ) ) . '/maintenance/Maintenance.php';

class ImportDummyData extends Maintenance {
	/** @var \BlueSpice\ExtendedStatistics\AttributeRegistryFactory */
	private $providerFactory;
	/** @var \BlueSpice\ExtendedStatistics\ISnapshotStore */
	private $snapshotStore;
	/** @var NamespaceInfo */
	private NamespaceInfo $namespaceInfo;

	/**
	 * @var array
	 */
	private $collection = [];

	/**
	 *
	 * @var array
	 */
	private $terms = [
		'income tax', 'quickbooks', 'account', 'accountant', 'cma', 'tax return',
		'payroll', 'bookkeeping', 'chartered accountant', 'accountant salary'
	];

	/** @var IDatabase */
	private $dbr = null;

	/** @var string[] */
	private $userList = [];

	/** @var string[] */
	private $pageList = [];

	/** @var string[] */
	private $namespaceList = [];

	/** @var string[] */
	private $categoryList = [];

	/** @var SnapshotFactory */
	private SnapshotFactory $snapshotFactory;

	/**
	 *
	 */
	public function __construct() {
		parent::__construct();
		$this->requireExtension( 'BlueSpiceExtendedStatistics' );
		$this->addOption( 'days', 'Number of days to mock', false, true );
	}

	/**
	 * @return void
	 * @throws MWException
	 * @throws ReflectionException
	 */
	public function execute() {
		$this->setServices();

		$this->dbr = $this->getDB( DB_REPLICA );
		$this->loadUserList();
		$this->loadPageList();
		$this->loadNamespaceList();
		$this->loadCategoryList();

		$this->parameters->addOption( 'term', implode( ',', $this->terms ) );

		foreach ( $this->providerFactory->getAll() as $key => $provider ) {
			$this->processProvider( $key, $provider );
		}
	}

	private function loadUserList() {
		$res = $this->dbr->select(
			'user',
			'user_name',
			'',
			__METHOD__
		);
		foreach ( $res as $row ) {
			$this->userList[] = $row->user_name;
		}
	}

	private function loadPageList() {
		$res = $this->dbr->select(
			'page',
			'*',
			[ 'page_content_model' => 'wikitext' ],
			__METHOD__
		);
		foreach ( $res as $row ) {
			$title = Title::newFromRow( $row );
			$this->pageList[] = $title->getPrefixedDBkey();
		}
	}

	private function loadCategoryList() {
		$res = $this->dbr->select(
			'category',
			'cat_title',
			'',
			__METHOD__
		);
		foreach ( $res as $row ) {
			$this->categoryList[] = $row->cat_title;
		}
	}

	private function loadNamespaceList() {
		$namespaces = $this->namespaceInfo->getContentNamespaces();
		foreach ( $namespaces as $idx ) {
			$namespaceName = $this->namespaceInfo->getCanonicalName( $idx );
			$namespaceName = empty( $namespaceName ) ? '-' : $namespaceName;
			$this->namespaceList[] = $namespaceName;
		}
		$this->namespaceList = array_unique( $this->namespaceList );
	}

	/**
	 *
	 * @param string $key
	 * @param ISnapshotProvider $provider
	 *
	 * @return void
	 * @throws Exception
	 */
	private function processProvider( $key, ISnapshotProvider $provider ) {
		try {
			$template = $this->loadTemplate( $key );
		} catch ( Exception $e ) {
			$this->output( $e->getMessage() );
		}

		$days = (int)$this->getOption( 'days', 365 );
		$today = new DateTime( 'now' );
		$date = ( clone $today )->sub( new DateInterval( "P{$days}D" ) );
		while ( $date->format( 'Ymd' ) !== $today->format( 'Ymd' ) ) {
			$date->add( new DateInterval( "P1D" ) );

			if ( (int)$date->format( 'N' ) === 1 ) {
				$this->insertAggregate(
					$provider,
					Snapshot::INTERVAL_WEEK,
					( clone $date )->sub( new DateInterval( 'P1W' ) )
				);
			}
			if ( (int)$date->format( 'j' ) === 1 ) {
				$this->insertAggregate(
					$provider,
					Snapshot::INTERVAL_MONTH,
					( clone $date )->sub( new DateInterval( 'P1M' ) )
				);
			}
			if ( $date->format( 'jn' ) === "11" ) {
				$this->insertAggregate(
					$provider,
					Snapshot::INTERVAL_YEAR,
					( clone $date )->sub( new DateInterval( 'P1Y' ) )
				);
			}

			$snapshotDate = SnapshotDate::newFromFormat( $date->format( 'Ymd' ), 'Ymd' );
			$data = $this->processTemplate( $template );

			// Add hitDiff and previous hits to PageHits data
			if ( $key === PageHitsSnapshot::TYPE ) {
				$previous = $this->snapshotStore->getPrevious( clone $snapshotDate, PageHitsSnapshot::TYPE );

				foreach ( $data as $page => &$pageData ) {
					$pageData['hits'] = $pageData['hitDiff'];

					if ( $previous ) {
						$pageData['hits'] += $previous->getData()[$page]['hits'];
					}
				}
			}

			$this->insertSingle( $snapshotDate, $data, $key );
		}
	}

	/**
	 * @param string $key
	 *
	 * @return array
	 * @throws Exception
	 */
	private function loadTemplate( string $key ): array {
		$file = __DIR__ . '/../doc/snapshotData/' . $key . '.json';
		if ( !file_exists( $file ) ) {
			throw new Exception( "File $file does not exist!\n" );
		}

		$template = json_decode( file_get_contents( $file ), 1 );
		if ( !$template ) {
			$this->output( "Template for $key not readable!\n" );
			throw new Exception( "Template for $key not readable!\n" );
		}

		return $template;
	}

	/**
	 * @param array $template
	 * @return array
	 */
	private function processTemplate( $template ) {
		return $this->processValue( $template['members'] );
	}

	/**
	 * @param mixed $value
	 * @return array
	 */
	private function processValue( $value ) {
		if ( $this->isRandInt( $value ) ) {
			return $this->randInt( $value );
		}
		$data = [];
		if ( is_array( $value ) ) {
			if ( isset( $value['key'] ) ) {
				switch ( $value['key'] ) {
					case '{{{user}}}':
						$parsed = $this->userList;
						break;
					case '{{{page}}}':
						$parsed = $this->pageList;
						break;
					case '{{{namespace}}}':
						$parsed = $this->namespaceList;
						break;
					case '{{{category}}}':
						$parsed = $this->categoryList;
						break;
					default:
						$parsed = $this->parseKey( $value['key'] );
						break;
				}
				if ( is_string( $parsed ) ) {
					$data[$parsed] = $this->processValue( $value['value'] );
				}
				if ( is_array( $parsed ) ) {
					foreach ( $parsed as $key ) {
						$data[$key] = $this->processValue( $value['value'] );
					}
				}
			} else {
				foreach ( $value as $k => $v ) {
					$data[$k] = $this->processValue( $v );
				}
			}
		}

		return $data;
	}

	/**
	 * @param string $value
	 * @return bool
	 */
	private function isRandInt( $value ) {
		return is_string( $value ) && strpos( $value, '{{randint' ) === 0;
	}

	/**
	 * @param string $value
	 * @return int
	 */
	private function randInt( $value ) {
		$value = trim( $value, '{}' );
		$bits = explode( '|', $value );
		if ( count( $bits ) === 1 ) {
			return rand( 0, 100000 );
		}
		$limits = $bits[1];
		[ $low, $high ] = explode( ',', $limits );
		return rand( (int)$low, (int)$high );
	}

	private function setServices() {
		$services = MediaWikiServices::getInstance();
		$this->providerFactory = $services->getService(
			'ExtendedStatisticsSnapshotProviderFactory'
		);
		$this->snapshotStore = $services->getService( 'ExtendedStatisticsSnapshotStore' );
		$this->namespaceInfo = $services->getNamespaceInfo();
		$this->snapshotFactory = $services->getService( 'ExtendedStatisticsSnapshotFactory' );
	}

	/**
	 * @param string $key
	 * @return string|string[]|null
	 */
	private function parseKey( $key ) {
		$matches = [];
		if ( preg_match( '/\{\{\{(.*?)\}\}\}/', $key, $matches ) ) {
			$option = $this->getOption( $matches[1] );

			if ( !$option ) {
				return [];
			}

			$options = explode( ',', $this->getOption( $matches[1] ) );
			shuffle( $options );

			return $options;
		}

		return is_string( $key ) ? $key : null;
	}

	/**
	 * @param SnapshotDate $snapshotDate
	 * @param array $data
	 * @param string $key
	 *
	 * @return void
	 */
	private function insertSingle( SnapshotDate $snapshotDate, array $data, string $key ): void {
		$snapshot = $this->snapshotFactory->createSnapshot( $snapshotDate, $key, $data );
		$this->snapshotStore->persistSnapshot( $snapshot );

		if ( !isset( $this->collection[$key] ) ) {
			$this->collection[$key] = [];
		}
		$this->collection[$key][] = $snapshot;
	}

	/**
	 * @param ISnapshotProvider $provider
	 * @param string $interval
	 * @param DateTime $date
	 *
	 * @throws Exception
	 */
	private function insertAggregate(
		ISnapshotProvider $provider,
		string $interval,
		DateTime $date
	): void {
		if ( empty( $this->collection[$provider->getType()] ) ) {
			return;
		}

		$new = $provider->aggregate(
			$this->getSnapshotsForRange( $provider->getType(), $interval, $date ),
			$interval,
			SnapshotDate::newFromFormat( $date->format( 'Ymd' ), 'Ymd' )
		);
		$this->snapshotStore->persistSnapshot( $new );
	}

	/**
	 * @param string $type
	 * @param string $interval
	 * @param DateTime $date
	 *
	 * @return Snapshot[]
	 * @throws Exception
	 */
	private function getSnapshotsForRange( string $type, string $interval, DateTime $date ): array {
		$range = SnapshotDateRange::newFromFilterData( [
			'dateStart' => $date->format( 'Ymd' ),
			'dateEnd' => $date->format( 'Ymd' ),
		], $interval );

		return array_filter(
			$this->collection[$type],
			fn( Snapshot $snapshot ) => $snapshot->getDate() >= $range->getFrom() &&
			$snapshot->getDate() <= $range->getTo()
		);
	}
}

$maintClass = ImportDummyData::class;
require_once RUN_MAINTENANCE_IF_MAIN;
