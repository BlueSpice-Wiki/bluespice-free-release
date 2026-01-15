<?php

namespace MWStake\MediaWiki\Component\DataStore;

class NullTrimmer implements ITrimmer {

	/**
	 *
	 * @param \MWStake\MediaWiki\Component\DataStore\Record[] $dataSets
	 * @return \MWStake\MediaWiki\Component\DataStore\Record[]
	 */
	public function trim( $dataSets ) {
		return $dataSets;
	}
}
