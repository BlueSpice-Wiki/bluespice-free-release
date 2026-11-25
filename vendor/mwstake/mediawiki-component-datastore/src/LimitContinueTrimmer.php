<?php

namespace MWStake\MediaWiki\Component\DataStore;

use InvalidArgumentException;

class LimitContinueTrimmer implements ITrimmer {

	/**
	 * @var array|null
	 */
	protected ?array $nextContinue = null;

	/**
	 * @param int $limit
	 * @param array|null $continueFrom
	 * @param int $offset
	 */
	public function __construct(
		private readonly int $limit = 25,
		private readonly ?array $continueFrom = null,
		private readonly int $offset = 0
	) {
	}

	/**
	 * @param \MWStake\MediaWiki\Component\DataStore\Record[] $dataSets
	 * @return \MWStake\MediaWiki\Component\DataStore\Record[]
	 */
	public function trim( $dataSets ) {
		$set = $this->getRelevantSet( $dataSets );
		if ( $this->getLimit() === -1 ) {
			return $set;
		}
		$result = array_slice( $set, 0, $this->getLimit() );
		if ( !empty( $result ) ) {
			if ( $result[0] instanceof IContinueAwareRecord ) {
				$this->nextContinue = [];
			}
		}
		// Get next result, if exists, for continue value
		if ( count( $set ) > $this->getLimit() ) {
			$nextRecord = $set[$this->getLimit()];
			if ( $nextRecord instanceof IContinueAwareRecord ) {
				$this->nextContinue = $nextRecord->getContinueValue();
			}
		}

		return $result;
	}

	/**
	 * @return array|null
	 */
	public function getContinue(): ?array {
		return $this->nextContinue;
	}

	/**
	 * @param array $dataSets
	 * @return array
	 */
	protected function getRelevantSet( array $dataSets ): array {
		if ( $this->continueFrom ) {
			$dataSets = $this->stripUntilContinue( $dataSets, $this->continueFrom );
		} elseif ( $this->offset > 0 ) {
			$dataSets = array_slice( $dataSets, $this->offset );
		}
		return $dataSets;
	}

	/**
	 * @param array $dataSets
	 * @param array $continue
	 * @return array
	 */
	private function stripUntilContinue( array $dataSets, array $continue ): array {
		foreach ( $dataSets as $index => $record ) {
			if ( !( $record instanceof IContinueAwareRecord ) ) {
				throw new InvalidArgumentException(
					'Continue value can be used only with records implementing IContinueAwareRecord'
				);
			}
			if ( $record->matchesContinueValue( $continue ) ) {
				return array_slice( $dataSets, $index );
			}
		}

		// Maybe throw error? this is unexpected behaviour
		return $dataSets;
	}

	/**
	 * @return int
	 */
	protected function getLimit(): int {
		return $this->limit;
	}
}
