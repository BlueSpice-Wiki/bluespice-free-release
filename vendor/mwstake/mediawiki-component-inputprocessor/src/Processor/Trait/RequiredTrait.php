<?php

namespace MWStake\MediaWiki\Component\InputProcessor\Processor\Trait;

use StatusValue;

trait RequiredTrait {

	/** @var bool */
	protected bool $required = false;

	/** @var mixed */
	protected mixed $defaultValue = null;

	/**
	 * @param bool $required
	 * @return static
	 */
	public function setRequired( bool $required ): static {
		$this->required = $required;
		return $this;
	}

	public function setDefaultValue( mixed $value ): static {
		$this->defaultValue = $value;
		return $this;
	}

	/**
	 * @param string|null $value
	 * @param string $fieldKey
	 * @return StatusValue
	 */
	protected function checkRequired( mixed $value, string $fieldKey ): StatusValue {
		if ( $this->required && ( $value === null || $value === '' ) ) {
			return StatusValue::newFatal( 'inputprocessor-error-value-required', $fieldKey );
		}
		return StatusValue::newGood();
	}

	/**
	 * @return bool
	 */
	protected function isRequired(): bool {
		return $this->required;
	}

	/**
	 * @return mixed
	 */
	protected function getDefaultValue(): mixed {
		return $this->defaultValue;
	}

	/**
	 * @return array
	 */
	protected function serializeRequiredSpec(): array {
		return [
			'required' => $this->isRequired(),
			'default' => $this->getDefaultValue(),
		];
	}
}
