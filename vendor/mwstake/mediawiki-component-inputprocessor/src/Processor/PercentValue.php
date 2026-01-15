<?php

namespace MWStake\MediaWiki\Component\InputProcessor\Processor;

use MWStake\MediaWiki\Component\InputProcessor\GenericProcessor;
use StatusValue;

class PercentValue extends IntValue {

	/**
	 * @inheritDoc
	 */
	public function initializeFromSpec( array $spec ): static {
		parent::initializeFromSpec( $spec );
		$this->setDefaultValue( $spec['default'] ?? 0 );
		return $this;
	}

	public function process( mixed $value, string $fieldKey ): StatusValue {
		$value = str_replace( '%', '', $value );
		return parent::process( $value, $fieldKey );
	}

	/**
	 * @return mixed
	 */
	public function jsonSerialize(): mixed {
		return array_merge( parent::jsonSerialize(), [
			'type' => 'percent',
			'min' => 0,
			'max' => 100,
		] );
	}
}
