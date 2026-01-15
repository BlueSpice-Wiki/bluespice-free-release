<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface\Hook;

interface MWStakeCommonUILessVarsOverride {

	/**
	 * Allows to override default values
	 *
	 * @param LessVars $lessVars
	 * @return void
	 */
	public function onMWStakeCommonUILessVarsOverride( $lessVars ): void;
}
