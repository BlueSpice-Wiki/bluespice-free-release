<?php
namespace BlueSpice\Discovery\Hook\BsAdapterAjaxPingResult;

use BlueSpice\Hook\BsAdapterAjaxPingResult;

class CheckAttentionIndicator extends BsAdapterAjaxPingResult {

	/**
	 * @return bool
	 */
	protected function doProcess() {
		if ( empty( $this->params['indicators'] ) ) {
			return true;
		}
		$indicators = (array)$this->params['indicators'];
		$factory = $this->getServices()->getService( 'BSAttentionIndicatorFactory' );
		foreach ( $indicators as $key ) {
			$indiactor = $factory->get( $key, $this->getContext()->getUser() );
			$response[$key] = $indiactor->hasIndication();
		}

		$this->singleResults['indicators'] = $response;
		$this->singleResults['success'] = true;

		return true;
	}

	/**
	 * @return bool
	 */
	protected function skipProcessing() {
		if ( $this->reference !== "AttentionIndicator" ) {
			return true;
		}

		return false;
	}
}
