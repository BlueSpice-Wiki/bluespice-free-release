<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs\Data\TitleTreeStore;

use Collation;
use MediaWiki\Collation\CollationFactory;
use MediaWiki\Language\Language;
use MWStake\MediaWiki\Component\CommonWebAPIs\ContentLanguageCollationTrait;
use MWStake\MediaWiki\Component\CommonWebAPIs\Data\TitleQueryStore\TitleRecord;

class SortkeyBucketProvider {
	use ContentLanguageCollationTrait;

	/** @var Collation */
	private $collation;

	/**
	 * @param CollationFactory $collationFactory
	 * @param Language $contentLanguage
	 */
	public function __construct( CollationFactory $collationFactory, Language $contentLanguage ) {
		$this->collation = $this->resolveCollation( $collationFactory, $contentLanguage );
	}

	/**
	 * Get the ordered list of distinct first-letter buckets from a set of TitleRecords,
	 * each with the continue value of the first matching record.
	 * Special characters are grouped into "#" and sorted at the end, numbers ("0-9") likewise.
	 *
	 * @param TitleRecord[] $records
	 * @return array[] Array of [ 'letter' => string, 'continue' => array ]
	 */
	public function getBuckets( array $records ): array {
		$buckets = [];
		foreach ( $records as $record ) {
			$letter = $record->get( TitleRecord::SORTKEY );
			if ( $letter === '' ) {
				continue;
			}
			if ( !isset( $buckets[$letter] ) ) {
				$buckets[$letter] = $record->getContinueValue();
			}
		}

		$letters = array_keys( $buckets );
		usort( $letters, function ( $a, $b ) {
			$aWeight = $this->getSortWeight( $a );
			$bWeight = $this->getSortWeight( $b );
			if ( $aWeight !== $bWeight ) {
				return $aWeight <=> $bWeight;
			}
			return strcmp(
				$this->collation->getSortKey( $a ),
				$this->collation->getSortKey( $b )
			);
		} );

		$sortedBuckets = [];
		foreach ( $letters as $letter ) {
			$sortedBuckets[$letter] = $buckets[$letter];
		}

		return $sortedBuckets;
	}

	/**
	 * Sort weight: letters first (0), numbers at the end (1), special chars last (2).
	 *
	 * @param string $letter
	 * @return int
	 */
	private function getSortWeight( string $letter ): int {
		if ( $letter === '0-9' ) {
			return 1;
		}
		if ( $letter === '#' ) {
			return 2;
		}
		return 0;
	}
}
