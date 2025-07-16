<?php

use BlueSpice\ExtendedStatistics\AttributeRegistryFactory;
use BlueSpice\ExtendedStatistics\ISnapshotProvider;
use BlueSpice\ExtendedStatistics\ISnapshotStore;
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

	/** @var AttributeRegistryFactory */
	private AttributeRegistryFactory $providerFactory;

	/** @var ISnapshotStore */
	private ISnapshotStore $snapshotStore;

	/** @var NamespaceInfo */
	private NamespaceInfo $namespaceInfo;

	/**
	 * @var array
	 */
	private $collection = [];

	/**
	 * @var array
	 */
	private $terms = [
		'income tax',
		'quickbooks',
		'account',
		'accountant',
		'cma',
		'tax return',
		'payroll',
		'bookkeeping',
		'chartered accountant',
		'accountant salary'
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
	 * @throws Exception
	 */
	public function execute() {
		$this->setServices();

		$this->dbr = $this->getDB( DB_REPLICA );
		$this->loadUserList();
		$this->loadPageList();
		$this->loadNamespaceList();
		$this->loadCategoryList();

		$this->parameters->addOption( 'term', implode( ',', $this->terms ) );

		foreach ( $this->providerFactory->getAll() as $provider ) {
			$this->processProvider( $provider );
		}
	}

	/**
	 * @return void
	 */
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

	/**
	 * @return void
	 */
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

	/**
	 * @return void
	 */
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

	/**
	 * @return void
	 */
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
	 * @param ISnapshotProvider $provider
	 *
	 * @return void
	 * @throws DateInvalidOperationException
	 * @throws Exception
	 */
	private function processProvider( ISnapshotProvider $provider ): void {
		$type = $provider->getType();

		try {
			$template = $this->loadTemplate( $type );
		} catch ( Exception $e ) {
			$this->output( $e->getMessage() );
		}

		$days = (int)$this->getOption( 'days', 365 );
		$today = new DateTime( 'now' );
		$date = ( clone $today )->sub( new DateInterval( "P{$days}D" ) );

		$this->forEachDayBetween(
			$date,
			$today,
			fn ( DateTime $date ) => $this->processDummyDay( $type, $template, $date )
		);

		$this->forEachDayBetween(
			$date,
			$today,
			fn ( DateTime $date ) => $this->processDummyAggregate( $provider, $date )
		);
	}

	/**
	 * @param string $type
	 * @param array $template
	 * @param DateTime $date
	 *
	 * @return void
	 * @throws Exception
	 */
	private function processDummyDay(
		string $type,
		array $template,
		DateTime $date
	): void {
		$snapshotDate = SnapshotDate::newFromFormat( $date->format( 'Ymd' ), 'Ymd' );
		$data = $this->processTemplate( $template );

		// Add hitDiff and previous hits to PageHits data
		if ( $type === PageHitsSnapshot::TYPE ) {
			$previous = $this->snapshotStore->getPrevious( clone $snapshotDate, PageHitsSnapshot::TYPE );

			foreach ( $data as $page => &$pageData ) {
				$pageData[ 'hits' ] = $pageData[ 'hitDiff' ];

				if ( $previous ) {
					$pageData[ 'hits' ] += $previous->getData()[ $page ][ 'hits' ];
				}
			}
		}

		$this->insertSingle( $snapshotDate, $data, $type );
	}

	/**
	 * @param ISnapshotProvider $provider
	 * @param DateTime $date
	 *
	 * @return void
	 * @throws DateInvalidOperationException
	 * @throws Exception
	 */
	private function processDummyAggregate(
		ISnapshotProvider $provider,
		DateTime $date
	): void {
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
	}

	/**
	 * Iterates through dates between a start and end date (inclusive of start, exclusive of end).
	 *
	 * @param DateTimeInterface $startDate The starting date.
	 * @param DateTimeInterface $endDate The ending date (loop stops before this date).
	 * @param callable $callback A function to execute for each day. It receives the current DateTime object.
	 */
	private function forEachDayBetween(
		DateTimeInterface $startDate,
		DateTimeInterface $endDate,
		callable $callback
	): void {
		$currentDate = clone $startDate;

		while ( $currentDate->format( 'Ymd' ) !== $endDate->format( 'Ymd' ) ) {
			$callback( $currentDate );
			$currentDate->add( new DateInterval( "P1D" ) );
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
	 *
	 * @return array
	 */
	private function processTemplate( $template ) {
		return $this->processValue( $template[ 'members' ] );
	}

	/**
	 * @param mixed $value
	 *
	 * @return array
	 */
	private function processValue( $value ) {
		if ( $this->isRandInt( $value ) ) {
			return $this->randInt( $value );
		}
		$data = [];
		if ( is_array( $value ) ) {
			if ( isset( $value[ 'key' ] ) ) {
				switch ( $value[ 'key' ] ) {
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
						$parsed = $this->parseKey( $value[ 'key' ] );
						break;
				}
				if ( is_string( $parsed ) ) {
					$data[ $parsed ] = $this->processValue( $value[ 'value' ] );
				}
				if ( is_array( $parsed ) ) {
					foreach ( $parsed as $key ) {
						$data[ $key ] = $this->processValue( $value[ 'value' ] );
					}
				}
			} else {
				foreach ( $value as $k => $v ) {
					$data[ $k ] = $this->processValue( $v );
				}
			}
		}

		return $data;
	}

	/**
	 * @param string $value
	 *
	 * @return bool
	 */
	private function isRandInt( $value ) {
		return is_string( $value ) && strpos( $value, '{{randint' ) === 0;
	}

	/**
	 * @param string $value
	 *
	 * @return int
	 */
	private function randInt( $value ) {
		$value = trim( $value, '{}' );
		$bits = explode( '|', $value );
		if ( count( $bits ) === 1 ) {
			return rand( 0, 100000 );
		}
		$limits = $bits[ 1 ];
		[
			$low,
			$high
		] = explode( ',', $limits );

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
	 *
	 * @return string|string[]|null
	 */
	private function parseKey( $key ) {
		$matches = [];
		if ( preg_match( '/\{\{\{(.*?)\}\}\}/', $key, $matches ) ) {
			$option = $this->getOption( $matches[ 1 ] );

			if ( !$option ) {
				return [];
			}

			$options = explode( ',', $this->getOption( $matches[ 1 ] ) );
			shuffle( $options );

			return $options;
		}

		return is_string( $key ) ? $key : null;
	}

	/**
	 * @param SnapshotDate $snapshotDate
	 * @param array $data
	 * @param string $type
	 *
	 * @return void
	 */
	private function insertSingle( SnapshotDate $snapshotDate, array $data, string $type ): void {
		$snapshot = $this->snapshotFactory->createSnapshot( $snapshotDate, $type, $data );
		$this->snapshotStore->persistSnapshot( $snapshot );

		if ( !isset( $this->collection[ $type ] ) ) {
			$this->collection[ $type ] = [];
		}
		$this->collection[ $type ][] = $snapshot;
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
		if ( empty( $this->collection[ $provider->getType() ] ) ) {
			return;
		}

		$range = SnapshotDateRange::newFromFilterData( [
			'dateStart' => $date->format( 'Ymd' ),
			'dateEnd' => $date->format( 'Ymd' ),
		], $interval );

		$snapshots = $this->snapshotStore->getSnapshotForRange( $range, $provider->getType() );

		if ( empty( $snapshots ) ) {
			return;
		}

		$new = $provider->aggregate(
			$snapshots,
			$interval,
			SnapshotDate::newFromFormat( $date->format( 'Ymd' ), 'Ymd' )
		);

		$this->snapshotStore->persistSnapshot( $new );
	}
}

$maintClass = ImportDummyData::class;
require_once RUN_MAINTENANCE_IF_MAIN;
