<?php

namespace BlueSpice\InsertCategory\HookHandler;

use MediaWiki\Hook\MediaWikiServicesHook;
use MediaWiki\Revision\SlotRoleRegistry;

class RegisterCategorySlot implements MediaWikiServicesHook {

	/**
	 * @inheritDoc
	 */
	public function onMediaWikiServices( $services ) {
		$services->addServiceManipulator(
			'SlotRoleRegistry',
			static function ( SlotRoleRegistry $registry ) {
				if ( $registry->isDefinedRole( 'category_storage' ) ) {
					return;
				}
				$registry->defineRoleWithModel(
					'category_storage',
					'category_storage',
					[
						'display' => 'none'
					]
				);
			}
		);
	}
}
