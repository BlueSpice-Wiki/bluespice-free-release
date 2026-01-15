<?php

namespace BlueSpice\Discovery\HookHandler\MWStakeCommonUIRegisterSkinSlotComponents;

use BlueSpice\Discovery\Component\SpecialAllTemplates;
use BlueSpice\Discovery\Component\SpecialSpecialPages;
use BlueSpice\Discovery\Component\SpecialUpload;
use MediaWiki\SpecialPage\SpecialPageFactory;
use MWStake\MediaWiki\Component\CommonUserInterface\Hook\MWStakeCommonUIRegisterSkinSlotComponents;

class GlobalActions implements MWStakeCommonUIRegisterSkinSlotComponents {

	/** @var SpecialPageFactory */
	private $specialPageFactory;

	public function __construct( SpecialPageFactory $specialPageFactory ) {
		$this->specialPageFactory = $specialPageFactory;
	}

	/**
	 * @inheritDoc
	 */
	public function onMWStakeCommonUIRegisterSkinSlotComponents( $registry ): void {
		$specialPageFactory = $this->specialPageFactory;
		$registry->register(
			'GlobalActionsOverview',
			[
				'special-specialpages' => [
					'factory' => static function () use ( $specialPageFactory ) {
						return new SpecialSpecialPages( $specialPageFactory );
					}
				],
				'special-alltemplates' => [
					'factory' => static function () {
						return new SpecialAllTemplates();
					}
				]
			]
		);
		$registry->register(
			'GlobalActionsEditing',
			[
				'special-upload' => [
					'factory' => static function () {
						return new SpecialUpload();
					}
				]
			]
		);
	}
}
