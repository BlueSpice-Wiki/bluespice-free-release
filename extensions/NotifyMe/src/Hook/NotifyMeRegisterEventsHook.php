<?php

namespace MediaWiki\Extension\NotifyMe\Hook;

interface NotifyMeRegisterEventsHook {
	/**
	 * Register events for the Notifications extension
	 *
	 * @param array &$events
	 */
	public function onNotifyMeRegisterEvents( array &$events ): void;
}
