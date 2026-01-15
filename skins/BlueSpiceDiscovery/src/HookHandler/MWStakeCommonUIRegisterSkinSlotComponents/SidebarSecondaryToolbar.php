<?php

namespace BlueSpice\Discovery\HookHandler\MWStakeCommonUIRegisterSkinSlotComponents;

use BlueSpice\Discovery\Component\ShareOptions;
use BlueSpice\Discovery\Component\Watch;
use MWStake\MediaWiki\Component\CommonUserInterface\Hook\MWStakeCommonUIRegisterSkinSlotComponents;

class SidebarSecondaryToolbar implements MWStakeCommonUIRegisterSkinSlotComponents {

	/**
	 * @inheritDoc
	 */
	public function onMWStakeCommonUIRegisterSkinSlotComponents( $registry ): void {
		$registry->register(
			'ToolbarPanel',
			[
				'watch' => [
					'factory' => static function () {
						return new Watch();
					}
				],
				'share' => [
					'factory' => static function () {
						return new ShareOptions();
					}
				]
			]
		);
	}
}
