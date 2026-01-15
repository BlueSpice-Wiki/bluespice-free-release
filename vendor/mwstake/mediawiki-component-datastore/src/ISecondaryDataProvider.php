<?php

namespace MWStake\MediaWiki\Component\DataStore;

interface ISecondaryDataProvider {

	/**
	 *
	 * @param \MWStake\MediaWiki\Component\DataStore\Record[] $dataSets
	 * @return \MWStake\MediaWiki\Component\DataStore\Record[]
	 */
	public function extend( $dataSets );
}
