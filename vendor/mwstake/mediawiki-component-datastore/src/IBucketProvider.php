<?php

namespace MWStake\MediaWiki\Component\DataStore;

interface IBucketProvider {

	/**
	 *
	 * @return array
	 */
	public function getBuckets(): array;
}
