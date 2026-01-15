<?php

namespace MWStake\MediaWiki\Component\InputProcessor\Processor;

use MWStake\MediaWiki\Component\InputProcessor\GenericProcessor;
use StatusValue;

class StringValue extends GenericProcessor {

	public function initializeFromSpec( array $spec ): static {
		parent::initializeFromSpec( $spec );
		$this->setDefaultValue( $spec['default'] ?? '' );
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
		if ( !is_string( $value ) ) {
			return StatusValue::newFatal( 'inputprocessor-error-string-not-string', $fieldKey, $value );
		}

		return StatusValue::newGood( $value );
	}

	/**
	 * @return mixed
	 */
	public function jsonSerialize(): mixed {
		return array_merge( parent::jsonSerialize(), [
			'type' => 'string',
		] );
	}
}
