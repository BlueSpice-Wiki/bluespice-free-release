<?php

namespace BlueSpice\PageAssignments\HookHandler;

use BlueSpice\PageAssignments\GlobalActionsEditing;
use MWStake\MediaWiki\Component\CommonUserInterface\Hook\MWStakeCommonUIRegisterSkinSlotComponents;

class CommonUserInterface implements MWStakeCommonUIRegisterSkinSlotComponents {

	/**
	 * @inheritDoc
	 */
	public function onMWStakeCommonUIRegisterSkinSlotComponents( $registry ): void {
		$registry->register(
			'GlobalActionsEditing',
			[
				'ga-bluespice-pageassignments' => [
					'factory' => static function () {
						return new GlobalActionsEditing();
					}
				]
			]
		);
	}

}
