<?php

namespace MWStake\MediaWiki\Component\DataStore;

use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;

interface IContinueAwareRecord extends IRecord {

	/**
	 * Get title represented by the record
	 *
	 * @param TitleFactory $titleFactory
	 * @return Title|null if cannot be created (will be skipped)
	 */
	public function getTitle( TitleFactory $titleFactory ): ?Title;

	/**
	 * Get array of values necessary to uniquely identify the record for the purpose of continue value
	 * @return array
	 */
	public function getContinueValue(): array;

	/**
	 * Check if the record matches the continue value, i.e. if it last shown record in previous set
	 *
	 * @param array $continueValue
	 * @return bool
	 */
	public function matchesContinueValue( array $continueValue ): bool;
}
