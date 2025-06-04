<?php

namespace MediaWiki\Extension\FlexiSkin\HookHandler;

use MediaWiki\Extension\FlexiSkin\FlexiSkinManager;
use MediaWiki\Extension\FlexiSkin\GlobalActionsAdministration;
use MediaWiki\Extension\FlexiSkin\IFlexiSkin;
use MWStake\MediaWiki\Component\CommonUserInterface\Hook\MWStakeCommonUILessVarsOverride;
use MWStake\MediaWiki\Component\CommonUserInterface\Hook\MWStakeCommonUIRegisterSkinSlotComponents;

class DiscoverySkin implements MWStakeCommonUIRegisterSkinSlotComponents, MWStakeCommonUILessVarsOverride {

	/** @var FlexiSkinManager */
	private $flexiSkinManager;

	/**
	 *
	 * @param FlexiSkinManager $flexiSkinManager
	 */
	public function __construct( FlexiSkinManager $flexiSkinManager ) {
		$this->flexiSkinManager = $flexiSkinManager;
	}

	/**
	 * @inheritDoc
	 */
	public function onMWStakeCommonUIRegisterSkinSlotComponents( $registry ): void {
		$registry->register(
			'GlobalActionsAdministration',
			[
				'bs-special-flexiskin' => [
					'factory' => static function () {
						return new GlobalActionsAdministration();
					}
				]
			]
		);
	}

	/**
	 * @inheritDoc
	 */
	public function onMWStakeCommonUILessVarsOverride( $lessVars ): void {
		$active = $this->flexiSkinManager->getActive();
		if ( !$active instanceof IFlexiSkin ) {
			return;
		}
		$vars = $this->flexiSkinManager->getActiveLessVars();
		foreach ( $vars as $var => $value ) {
			$lessVars->setVar( $var, $value );
		}
	}
}
