<?php

namespace BlueSpice\InstanceStatus\Hook;

use MediaWiki\SpecialPage\SpecialPageFactory;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\RestrictedTextLink;
use MWStake\MediaWiki\Component\CommonUserInterface\Hook\MWStakeCommonUIRegisterSkinSlotComponents;

class AddGlobalAction implements MWStakeCommonUIRegisterSkinSlotComponents {

	/** @var SpecialPageFactory */
	private $specialPageFactory;

	/**
	 * @param SpecialPageFactory $specialPageFactory
	 */
	public function __construct( SpecialPageFactory $specialPageFactory ) {
		$this->specialPageFactory = $specialPageFactory;
	}

	/**
	 * @inheritDoc
	 */
	public function onMWStakeCommonUIRegisterSkinSlotComponents( $registry ): void {
		$special = $this->specialPageFactory->getPage( 'InstanceStatus' );
		if ( !$special ) {
			return;
		}
		$registry->register(
			'GlobalActionsAdministration',
			[
				'bs-instance-status' => [
					'factory' => static function () use ( $special ) {
						return new RestrictedTextLink( [
							'id' => 'ga-bs-instance-status',
							'href' => $special->getPageTitle()->getLocalURL(),
							'text' => $special->getDescription(),
							'title' => $special->getDescription(),
							'aria-label' => $special->getDescription(),
							'permissions' => [ 'wikiadmin' ]
						] );
					}
				]
			]
		);
	}
}
