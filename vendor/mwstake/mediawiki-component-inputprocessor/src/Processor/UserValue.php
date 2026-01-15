<?php

namespace MWStake\MediaWiki\Component\InputProcessor\Processor;

use MediaWiki\User\UserFactory;
use StatusValue;

class UserValue extends StringValue {

	/** @var UserFactory */
	protected UserFactory $userFactory;

	/** @var bool */
	protected bool $mustExist = true;

	/**
	 * @param UserFactory $userFactory
	 */
	public function __construct( UserFactory $userFactory ) {
		$this->userFactory = $userFactory;
	}

	/**
	 * @param array $spec
	 * @return $this
	 */
	public function initializeFromSpec( array $spec ): static {
		parent::initializeFromSpec( $spec );
		$this->setMustExist( $spec['mustExist'] ?? false );
		return $this;
	}

	/**
	 * @param bool $mustExist
	 * @return $this
	 */
	public function setMustExist( bool $mustExist ): static {
		$this->mustExist = $mustExist;
		return $this;
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
		$user = $this->userFactory->newFromName( $value );
		if ( !$user ) {
			return StatusValue::newFatal( 'inputprocessor-error-user-invalid', $fieldKey, $value );
		}
		if ( $this->mustExist && !$user->isRegistered() ) {
			return StatusValue::newFatal( 'inputprocessor-error-user-not-exist', $fieldKey, $value );
		}

		return StatusValue::newGood( $user );
	}

	/**
	 * @return mixed
	 */
	public function jsonSerialize(): mixed {
		return array_merge( parent::jsonSerialize(), [
			'type' => 'user',
			'mustExist' => $this->mustExist,
		] );
	}
}
