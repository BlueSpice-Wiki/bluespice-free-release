<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface\Hook;

interface MWStakeCommonUIRegisterSkinSlotComponents {

	/**
	 *
	 * @param ISkinSlotRegistry $registry
	 * @return void
	 */
	public function onMWStakeCommonUIRegisterSkinSlotComponents( $registry ): void;
}
