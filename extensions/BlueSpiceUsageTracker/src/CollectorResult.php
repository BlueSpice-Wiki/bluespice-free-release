<?php
namespace BS\UsageTracker;

class CollectorResult {
	/**
	 *
	 * @var int
	 */
	public $count = 0;

	/**
	 *
	 * @var string
	 */
	public $description = '';

	/**
	 *
	 * @var string
	 */
	public $identifier = '';

	/**
	 *
	 * @var string
	 */
	public $type = '';

	/**
	 *
	 * @var string
	 */
	public $updateDate = '';

	/**
	 *
	 * @param \BS\UsageTracker\Collectors\Base|null $oCollector
	 */
	public function __construct( $oCollector = null ) {
		if ( is_object( $oCollector ) && ( $oCollector instanceof Collectors\Base ) ) {
			$this->description = $oCollector->getDescription();
			$this->identifier = $oCollector->getIdentifier();
			$this->updateDate = wfTimestamp();
			$this->type = get_class( $oCollector );
		}
	}

	/**
	 *
	 * @param \Wikimedia\Rdbms\ResultWrapper $oRow
	 * @return CollectorResult
	 */
	public static function newFromDBRow( $oRow ) {
		$oResult = new self();
		$collectorClass = $oRow->ut_type;
		$oCollector = new $collectorClass();
		$oResult->description = $oCollector->getDescription();
		$oResult->identifier = $oRow->ut_identifier;
		$oResult->type = $oRow->ut_type;
		$oResult->updateDate = $oRow->ut_timestamp;
		$oResult->count = $oRow->ut_count;
		unset( $oCollector );
		return $oResult;
	}

	/**
	 *
	 * @return string|bool
	 */
	public function getUpdateDate() {
		return $this->updateDate;
	}

	/**
	 *
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}
}
