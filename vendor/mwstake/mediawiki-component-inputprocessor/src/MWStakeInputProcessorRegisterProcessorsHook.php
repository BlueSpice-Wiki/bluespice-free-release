<?php

namespace MWStake\MediaWiki\Component\InputProcessor;

interface MWStakeInputProcessorRegisterProcessorsHook {

	/**
	 * @param array &$registry
	 * @return void
	 */
	public function onMWStakeInputProcessorRegisterProcessors( &$registry ): void;
}
