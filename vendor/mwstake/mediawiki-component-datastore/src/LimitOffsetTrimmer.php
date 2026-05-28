<?php

namespace MWStake\MediaWiki\Component\DataStore;

/**
 * @deprecated since 3.0.0 - use LimitContinueTrimmer with offset instead
 */
class LimitOffsetTrimmer extends LimitContinueTrimmer {

	/**
	 * @param int $limit
	 * @param int $offset
	 */
	public function __construct( int $limit = 25, int $offset = 0 ) {
		parent::__construct( $limit, [], $offset );
	}
}
