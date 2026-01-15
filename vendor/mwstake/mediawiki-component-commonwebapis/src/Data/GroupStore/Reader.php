<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs\Data\GroupStore;

use MediaWiki\Config\GlobalVarConfig;
use MWStake\MediaWiki\Component\DataStore\ReaderParams;
use MWStake\MediaWiki\Component\Utils\Utility\GroupHelper;

class Reader extends \MWStake\MediaWiki\Component\DataStore\Reader {
	/** @var \MWStake\MediaWiki\Component\Utils\Utility\GroupHelper */
	protected $groupHelper;

	/** @var GlobalVarConfig */
	protected $mwsgConfig;

	/**
	 * @param GroupHelper $groupHelper
	 * @param GlobalVarConfig $mwsgConfig
	 */
	public function __construct( GroupHelper $groupHelper, GlobalVarConfig $mwsgConfig ) {
		$this->groupHelper = $groupHelper;
		$this->mwsgConfig = $mwsgConfig;
	}

	/**
	 * @return UserSchema
	 */
	public function getSchema() {
		return new GroupSchema();
	}

	/**
	 * @param ReaderParams $params
	 *
	 * @return PrimaryDataProvider
	 */
	public function makePrimaryDataProvider( $params ) {
		return new PrimaryDataProvider( $this->groupHelper, $this->mwsgConfig );
	}

	/**
	 * @inheritDoc
	 */
	public function makeSecondaryDataProvider() {
		return null;
	}
}
