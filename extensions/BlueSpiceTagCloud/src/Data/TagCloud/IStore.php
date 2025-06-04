<?php

namespace BlueSpice\TagCloud\Data\TagCloud;

use BlueSpice\TagCloud\Context;

interface IStore extends \MWStake\MediaWiki\Component\DataStore\IStore {

	/**
	 *
	 * @param Context $context
	 */
	public function __construct( Context $context );

	/**
	 *
	 * @param array $params
	 * @return ReaderParams
	 */
	public function makeReaderParams( array $params = [] );
}
