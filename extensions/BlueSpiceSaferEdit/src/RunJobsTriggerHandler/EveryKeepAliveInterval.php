<?php

namespace BlueSpice\SaferEdit\RunJobsTriggerHandler;

use BlueSpice\SaferEdit\KeepAliveWatcher;
use DateTime;
use MWStake\MediaWiki\Component\RunJobsTrigger\Interval;

class EveryKeepAliveInterval implements Interval {

	/**
	 * @param DateTime $currentRunTimestamp
	 * @param array $options
	 * @return DateTime
	 */
	public function getNextTimestamp( $currentRunTimestamp, $options ) {
		$next = clone $currentRunTimestamp;
		$next->modify( '+' . KeepAliveWatcher::SAFER_EDIT_KEEP_ALIVE_INTERVAL . ' seconds' );
		return $next;
	}
}
