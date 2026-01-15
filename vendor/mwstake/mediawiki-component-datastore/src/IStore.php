<?php

namespace MWStake\MediaWiki\Component\DataStore;

interface IStore {

	/**
	 * @return IWriter
	 */
	public function getWriter();

	/**
	 * @return IReader
	 */
	public function getReader();
}
