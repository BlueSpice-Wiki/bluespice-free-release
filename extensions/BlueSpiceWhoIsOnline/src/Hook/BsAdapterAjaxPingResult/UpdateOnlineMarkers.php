<?php

namespace BlueSpice\WhoIsOnline\Hook\BsAdapterAjaxPingResult;

use BlueSpice\WhoIsOnline\Tracer;

class UpdateOnlineMarkers extends \BlueSpice\Hook\BsAdapterAjaxPingResult {

	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		if ( $this->reference !== 'WhoIsOnline' ) {
			return true;
		}
		if ( !isset( $this->params[0] ) || empty( $this->params[0]['onlinemarkers'] ) ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		$this->singleResults['onlineMarkers'] = [];
		$this->singleResults['success'] = true;
		$userFactory = $this->getServices()->getUserFactory();
		foreach ( $this->params[0]['onlinemarkers'] as $userName => &$status ) {
			$user = $userFactory->newFromName( $userName );
			if ( !$user ) {
				return Tracer::ONLINE_STATUS_OFFLINE;
			}
			$status = $this->getTracer()->getUserOnlineStatus( $user );
		}
		$this->singleResults['onlinemarkers'] = $this->params[0]['onlinemarkers'];
	}

	/**
	 *
	 * @return Tracer
	 */
	private function getTracer() {
		return $this->getServices()->getService( 'BSWhoIsOnlineTracer' );
	}
}
