<?php

namespace MediaWiki\Extension\UserProfile\Hooks;

use MediaWiki\Extension\UserProfile\ProfileFieldRegistry;
use MediaWiki\Output\Hook\BeforePageDisplayHook;
use MediaWiki\Output\OutputPage;

class AddModules implements BeforePageDisplayHook {

	/**
	 * @var ProfileFieldRegistry
	 */
	private $profileFieldRegistry;

	/**
	 * @param ProfileFieldRegistry $profileFieldRegistry
	 */
	public function __construct( ProfileFieldRegistry $profileFieldRegistry ) {
		$this->profileFieldRegistry = $profileFieldRegistry;
	}

	/**
	 * @inheritDoc
	 */
	public function onBeforePageDisplay( $out, $skin ): void {
		$out->addModules( [ 'ext.userProfile.profileImage.bootstrap' ] );

		$out->addModuleStyles( [ 'ext.userProfile.styles' ] );
		if ( !$out->getTitle()->isSpecialPage() ) {
			$out->addJsConfigVars( 'wgUserProfileAvailableFields', $this->getAvailableFieldsForTag( $out ) );
		}
	}

	/**
	 * @param OutputPage $out
	 * @return array
	 */
	private function getAvailableFieldsForTag( OutputPage $out ): array {
		$allFields = $this->profileFieldRegistry->getSerializedFields(
			$out->getUser(), $out->getUser(), $out->getLanguage()
		);
		$mandatory = [ 'username', 'realName', 'userDisplay', 'imageUrl' ];

		// Filter out mandatory fields
		return array_filter( $allFields, function ( $field ) use ( $mandatory ) {
			return !in_array( $field['name'], $mandatory );
		} );
	}
}
