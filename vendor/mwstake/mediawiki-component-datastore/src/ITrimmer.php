<?php

 namespace MWStake\MediaWiki\Component\DataStore;

interface ITrimmer {

	/**
	 *
	 * @param \MWStake\MediaWiki\Component\DataStore\Record[] $dataSets
	 * @return \MWStake\MediaWiki\Component\DataStore\Record[]
	 */
	public function trim( $dataSets );
}
