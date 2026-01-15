<?php

namespace MWStake\MediaWiki\Component\InputProcessor\Processor;

use MediaWiki\User\UserGroupManager;
use StatusValue;

class UserGroupValue extends StringValue {

	/** @var UserGroupManager */
	protected UserGroupManager $userGroupManager;

	/**
	 * @param UserGroupManager $userGroupManager
	 */
	public function __construct( UserGroupManager $userGroupManager ) {
		$this->userGroupManager = $userGroupManager;
	}

	/**
	 * @inheritDoc
	 */
	public function process( mixed $value, string $fieldKey ): StatusValue {
		$parentStatus = parent::process( $value, $fieldKey );
		if ( !$parentStatus->isGood() ) {
			return $parentStatus;
		}
		if ( !$this->isRequired() && $value === null ) {
			return StatusValue::newGood( $this->getDefaultValue() );
		}
		$value = $parentStatus->getValue();
		$groups = array_merge(
			$this->userGroupManager->listAllGroups(),
			$this->userGroupManager->listAllImplicitGroups()
		);
		$lowercased = [];
		foreach ( $groups as $group ) {
			$lowercased[strtolower( $group )] = $group;
		}
		$labels = [];
		foreach ( $lowercased as $lc => $group ) {
			$msg = \Message::newFromKey( 'group-' . $group );
			if ( $msg->exists() ) {
				$labels[$lc] = $msg->text();
			}
		}

		$lcValue = strtolower( $value );
		if ( !isset( $lowercased[$lcValue] ) && !isset( $labels[$lcValue] ) ) {
			return StatusValue::newFatal( 'inputprocessor-error-usergroup-invalid', $fieldKey, $value );
		}

		return StatusValue::newGood( $lowercased[$lcValue] ?? $lcValue );
	}

	/**
	 * @return mixed
	 */
	public function jsonSerialize(): mixed {
		return array_merge( parent::jsonSerialize(), [
			'type' => 'user_group',
		] );
	}
}
