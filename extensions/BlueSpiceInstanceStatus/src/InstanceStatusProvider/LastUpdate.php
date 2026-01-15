<?php

namespace BlueSpice\InstanceStatus\InstanceStatusProvider;

use MediaWiki\Message\Message;

class LastUpdate extends Created {

	/**
	 * @return string
	 */
	public function getLabel(): string {
		return Message::newFromKey( 'bs-instancestatus-instance-status-last-update-label' )->text();
	}

	/**
	 * @return mixed|string
	 */
	protected function getConfigVar() {
		$filepath = $GLOBALS['IP'] . "/composer.lock";
		if ( !file_exists( $filepath ) ) {
			return '';
		}
		$ts = date( "YmdHis", filemtime( $filepath ) );
		return is_string( $ts ) ? $ts : '';
	}

	/**
	 * @return string
	 */
	public function getIcon(): string {
		return 'reload';
	}

	/**
	 * @return int
	 */
	public function getPriority(): int {
		return 50;
	}
}
