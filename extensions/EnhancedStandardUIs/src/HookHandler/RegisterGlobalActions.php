<?php

namespace MediaWiki\Extension\EnhancedStandardUIs\HookHandler;

use MediaWiki\Extension\EnhancedStandardUIs\FilelistGlobalAction;
use MWStake\MediaWiki\Component\CommonUserInterface\Hook\MWStakeCommonUIRegisterSkinSlotComponents;

class RegisterGlobalActions implements MWStakeCommonUIRegisterSkinSlotComponents {

	/**
	 * @inheritDoc
	 */
	public function onMWStakeCommonUIRegisterSkinSlotComponents( $registry ): void {
		$registry->register(
			'GlobalActionsOverview',
			[
				'special-enhanced-standard-uis-filelist' => [
					'factory' => static function () {
						return new FilelistGlobalAction();
					}
				]
			]
		);
	}
}
