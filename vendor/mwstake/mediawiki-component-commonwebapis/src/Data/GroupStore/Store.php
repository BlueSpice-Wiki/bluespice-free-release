<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs\Data\GroupStore;

use MediaWiki\Config\GlobalVarConfig;
use MediaWiki\HookContainer\HookContainer;
use MWStake\MediaWiki\Component\DataStore\IStore;
use MWStake\MediaWiki\Component\DataStore\Schema;
use MWStake\MediaWiki\Component\Utils\UtilityFactory;

/*
 * @stable to extend
 */
class Store implements IStore {
	/** @var \MWStake\MediaWiki\Component\Utils\Utility\GroupHelper */
	protected $groupHelper;

	/** @var GlobalVarConfig */
	protected $mwsgConfig;

	/** @var HookContainer */
	protected $hookContainer;

	/** @var bool */
	private $allowEveryone = false;

	/**
	 * @param UtilityFactory $utilityFactory
	 * @param GlobalVarConfig $mwsgConfig
	 * @param HookContainer $hookContainer
	 */
	public function __construct(
		UtilityFactory $utilityFactory, GlobalVarConfig $mwsgConfig, HookContainer $hookContainer
	) {
		$this->groupHelper = $utilityFactory->getGroupHelper();
		$this->mwsgConfig = $mwsgConfig;
		$this->hookContainer = $hookContainer;
	}

	/**
	 * @return Schema
	 */
	public function getSchema() {
		return new GroupSchema();
	}

	/**
	 * @param bool $allow
	 * @return void
	 */
	public function setAllowEveryone( bool $allow ): void {
		$this->allowEveryone = $allow;
	}

	/**
	 * @return \MWStake\MediaWiki\Component\DataStore\Reader
	 */
	public function getReader() {
		return new Reader( $this->groupHelper, $this->mwsgConfig, $this->hookContainer, $this->allowEveryone );
	}

	/**
	 * @return PrimaryDataProvider
	 */
	public function getWriter() {
		throw new NotImplementedException();
	}
}
