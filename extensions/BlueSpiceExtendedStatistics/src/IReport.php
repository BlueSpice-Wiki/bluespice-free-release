<?php

namespace BlueSpice\ExtendedStatistics;

interface IReport {

	/**
	 * Return type key for the provider
	 *
	 * @return string
	 */
	public function getSnapshotKey();

	/**
	 * Format data in a way that is usable by client chart
	 *
	 * @param Snapshot[] $snapshots
	 * @param array $filterData
	 * @param int $limit
	 * @return array
	 */
	public function getClientData( $snapshots, array $filterData, $limit = 20 ): array;

	/**
	 * Connection to the front-end
	 *
	 * @return ClientReportHandler
	 */
	public function getClientReportHandler(): ClientReportHandler;
}
