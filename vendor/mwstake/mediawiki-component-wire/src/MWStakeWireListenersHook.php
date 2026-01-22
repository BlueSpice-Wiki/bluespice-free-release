<?php

namespace MWStake\MediaWiki\Component\Wire;

interface MWStakeWireListenersHook {

	/**
	 * @param WireListenerRegistry $listenerRegistry
	 * @return mixed
	 */
	public function onMWStakeWireListeners( WireListenerRegistry $listenerRegistry );
}
