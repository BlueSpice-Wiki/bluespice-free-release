<?php

namespace MediaWiki\Extension\UserProfile\Hooks;

use MediaWiki\Hook\MediaWikiServicesHook;
use MediaWiki\Revision\SlotRoleRegistry;

class AddProfileSlot implements MediaWikiServicesHook {

	/**
	 *
	 * @inheritDoc
	 */
	public function onMediaWikiServices( $services ) {
		$services->addServiceManipulator(
			'SlotRoleRegistry',
			static function ( SlotRoleRegistry $registry ) {
				if ( $registry->isDefinedRole( SLOT_ROLE_USER_PROFILE ) ) {
					return;
				}
				$registry->defineRoleWithModel(
					SLOT_ROLE_USER_PROFILE,
					CONTENT_MODEL_USER_PROFILE,
					[
						'display' => 'none'
					]
				);
			}
		);
	}
}
