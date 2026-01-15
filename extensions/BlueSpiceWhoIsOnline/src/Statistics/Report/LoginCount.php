<?php

namespace BlueSpice\WhoIsOnline\Statistics\Report;

use BlueSpice\ExtendedStatistics\ClientReportHandler;
use BlueSpice\ExtendedStatistics\IReport;
use MediaWiki\Message\Message;

class LoginCount implements IReport {

	/**
	 * @inheritDoc
	 */
	public function getSnapshotKey() {
		return 'wo-logincount';
	}

	/**
	 * @inheritDoc
	 */
	public function getClientData( $snapshots, array $filterData, $limit = 20 ): array {
		// This report is always aggregated
		$processed = [];

		foreach ( $snapshots as $snapshot ) {
			$data = $snapshot->getData();
			$users = $data['users'];
			$usersCount = 0;
			foreach ( $users as $name => $duration ) {
				if ( $duration == '0' ) {
					continue;
				}
				$usersCount++;
			}
			$processed[] = [
				'name' => $snapshot->getDate()->forGraph(),
				'line' => Message::newFromKey( 'bs-whoisonline-statistics-report-login-number' )->plain(),
				'value' => $usersCount
			];
		}

		return $processed;
	}

	/**
	 * @inheritDoc
	 */
	public function getClientReportHandler(): ClientReportHandler {
		return new ClientReportHandler(
			[ 'ext.bluespice.whoisonline.statistics' ],
			'bs.whoisonline.report.LoginCountReport'
		);
	}
}
