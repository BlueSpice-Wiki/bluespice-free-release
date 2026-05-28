<?php

namespace MWStake\MediaWiki\Component\DataStore;

class ResultSet extends RecordSet {

	/** @var int */
	protected $total = 0;
	/** @var array */
	protected array $buckets = [];
	/** @var array|null */
	protected ?array $continueValue = null;
	/** @var bool */
	protected bool $totalApproximate = false;
	/** @var string|null */
	protected ?string $queryId = null;

	/**
	 *
	 * @param Record[] $records
	 * @param int $total
	 * @param array|null $continueValue
	 * @param bool $totalApproximate
	 * @param string|null $queryId
	 */
	public function __construct(
		$records, $total, ?array $continueValue = null, bool $totalApproximate = false, ?string $queryId = null
	) {
		parent::__construct( $records );
		$this->total = $total;
		$this->continueValue = $continueValue;
		$this->totalApproximate = $totalApproximate;
		$this->queryId = $queryId;
	}

	/**
	 *
	 * @return int
	 */
	public function getTotal() {
		return $this->total;
	}

	/**
	 * @param array $buckets
	 * @return void
	 */
	public function setBuckets( array $buckets ) {
		$this->buckets = $buckets;
	}

	/**
	 * @return array
	 */
	public function getBuckets(): array {
		return $this->buckets;
	}

	/**
	 * @return array
	 */
	public function getContinue(): ?array {
		return $this->continueValue;
	}

	/**
	 * @return bool
	 */
	public function isTotalApproximate(): bool {
		return $this->totalApproximate;
	}

	/**
	 * @return string|null
	 */
	public function getQueryId(): ?string {
		return $this->queryId;
	}
}
