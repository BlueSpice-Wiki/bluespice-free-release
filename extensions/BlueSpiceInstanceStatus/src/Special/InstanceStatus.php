<?php

namespace BlueSpice\InstanceStatus\Special;

use BlueSpice\InstanceStatus\StatusReport;
use MediaWiki\Html\Html;
use MediaWiki\Message\Message;
use MediaWiki\SpecialPage\SpecialPage;

class InstanceStatus extends SpecialPage {

	/** @var StatusReport */
	private StatusReport $report;

	/**
	 * @param StatusReport $report
	 */
	public function __construct( StatusReport $report ) {
		parent::__construct( 'InstanceStatus', 'wikiadmin' );
		$this->report = $report;
	}

	/**
	 * @inheritDoc
	 */
	public function execute( $page ) {
		parent::execute( $page );
		$html = Html::openElement( 'div', [
			'id' => 'bs-instance-status-overview'
		] );
		$html .= Html::element(
			'h3',
			[ 'id' => 'bs-instancestatus-overview-loader' ],
			Message::newFromKey( 'bs-instancestatus-overview-loader' )->text()
		);
		$html .= Html::closeElement( 'div' );
		$this->getOutput()->addHTML( $html );
		$this->addStatusData();
		$this->getOutput()->addModules( [ "ext.blueSpiceInstanceStatus.special" ] );
		return true;
	}

	/**
	 * @return void
	 */
	private function addStatusData() {
		$statusInfo = $this->report->getReportForUI();
		$this->getOutput()->addJsConfigVars( 'bsInstanceStatus_StatusData', $statusInfo );
	}
}
