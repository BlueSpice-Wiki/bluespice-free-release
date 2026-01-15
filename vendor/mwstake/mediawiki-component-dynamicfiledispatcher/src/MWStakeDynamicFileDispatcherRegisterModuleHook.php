<?php

namespace MWStake\MediaWiki\Component\DynamicFileDispatcher;

interface MWStakeDynamicFileDispatcherRegisterModuleHook {
	/**
	 * Register or override modules
	 * @param array &$modules
	 * @return void
	 */
	public function onMWStakeDynamicFileDispatcherRegisterModule( &$modules );
}
