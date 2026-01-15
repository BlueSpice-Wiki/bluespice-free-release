<?php

namespace BlueSpice\ExtendedStatistics\Special;

use BlueSpice\ExtendedStatistics\IReport;
use BlueSpice\ExtendedStatistics\SnapshotDate;
use DateInterval;
use MediaWiki\Html\Html;
use MediaWiki\MediaWikiServices;
use MediaWiki\SpecialPage\SpecialPage;

class AggregatedStatistic extends SpecialPage {
	/** @var AttributeRegistryFactory */
	private $reportFactory;

	public function __construct() {
		parent::__construct(
			'AggregatedStatistic',
			'extendedstatistics-viewspecialpage-aggregated'
		);
		$this->reportFactory = MediaWikiServices::getInstance()->getService(
			'ExtendedStatisticsReportFactory'
		);
	}

	/**
	 * @inheritDoc
	 */
	public function execute( $subPage ) {
		parent::execute( $subPage );
		$output = $this->getOutput();

		$reportModules = [];
		/**
		 * @var string $key
		 * @var IReport $report
		 */
		foreach ( $this->reportFactory->getAll() as $key => $report ) {
			$clientModule = $report->getClientReportHandler();
			$reportModules[$key] = [
				'rlModules' => $clientModule->getRLModules(),
				'class' => $clientModule->getClass(),
			];
		}
		$defaultFilters = [
			'date' => [
				'dateEnd' => ( new SnapshotDate() )->format( 'Y-m-d' ),
				'dateStart' => ( new SnapshotDate() )
					->sub( new DateInterval( 'P10D' ) )->format( 'Y-m-d' )
			],
			'interval' => 'day'
		];
		$output->addModules( [ 'ext.bluespice.aggregatedstatistics' ] );

		$output->enableOOUI();

		$output->addHTML(
			Html::element( 'div', [
				'id' => 'bs-extendedstatistics-special-aggregatedstatistics',
				'data-reports' => json_encode( $reportModules ),
				'data-default-filter' => json_encode( $defaultFilters ),
			] )
		);
	}

	/**
	 *
	 * @return string
	 */
	public function getGroupName() {
		return 'bluespice';
	}
}
