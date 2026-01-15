<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface\Hook;

interface MWStakeCommonUILessVarsInit {

	/**
	 * Set default values for LessVars
	 *
	 * @param LessVars $lessVars
	 * @return void
	 */
	public function onMWStakeCommonUILessVarsInit( $lessVars ): void;
}
