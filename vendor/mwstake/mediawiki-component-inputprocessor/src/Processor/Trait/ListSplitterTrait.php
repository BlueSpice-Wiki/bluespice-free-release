<?php

namespace MWStake\MediaWiki\Component\InputProcessor\Processor\Trait;

use StatusValue;

trait ListSplitterTrait {

	/** @var string|null */
	protected ?string $separator = null;

	/**
	 * @param string|null $separator
	 * @return static
	 */
	public function setListSeparator( ?string $separator ): static {
		$this->separator = $separator;
		return $this;
	}

	/**
	 * @param string $value
	 * @return array
	 */
	protected function splitList( string $value ): array {
		if ( $this->separator ) {
			$separators = [ $this->separator ];
		} else {
			$separators = [ ',', ';', '|' ];
		}

		// Split on separators
		$parts = preg_split( '/[' . preg_quote( implode( '', $separators ), '/' ) . ']/', $value );
		// Remove empty parts
		$parts = array_filter( $parts, 'strlen' );
		// Trim parts
		return array_map( 'trim', $parts );
	}

	/**
	 * @inheritDoc
	 */
	public function initializeFromSpec( array $spec ): static {
		parent::initializeFromSpec( $spec );
		$this->setListSeparator( $spec['separator'] ?? null );
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function process( mixed $value, string $fieldKey ): StatusValue {
		$required = $this->checkRequired( $value, $fieldKey );
		if ( !$required->isGood() ) {
			return $required;
		}
		if ( !$this->isRequired() && $value === null ) {
			return StatusValue::newGood( $this->getDefaultValue() ?? [] );
		}
		$values = $this->splitList( $value );
		$status = StatusValue::newGood();
		$processed = [];
		foreach ( $values as $value ) {
			$parentStatus = parent::process( $value, $fieldKey );
			if ( !$parentStatus->isGood() ) {
				$status->setOK( false );
				$status->merge( $parentStatus );
				continue;
			}
			$processed[] = $parentStatus->getValue();
		}
		if ( $status->isGood() ) {
			$status->setResult( true, $processed );
		}
		return $status;
	}

	/**
	 * @return array
	 */
	protected function serializeListSpec(): array {
		return [
			'is_list' => true,
			'separator' => $this->separator,
		];
	}

	/**
	 * @return mixed
	 */
	public function jsonSerialize(): mixed {
		return array_merge( parent::jsonSerialize(), $this->serializeListSpec() );
	}
}
