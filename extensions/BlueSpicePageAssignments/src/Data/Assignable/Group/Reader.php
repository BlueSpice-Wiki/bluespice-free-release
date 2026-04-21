<?php

namespace BlueSpice\PageAssignments\Data\Assignable\Group;

use MediaWiki\Config\GlobalVarConfig;
use MediaWiki\HookContainer\HookContainer;
use MediaWiki\Title\Title;
use MWStake\MediaWiki\Component\DataStore\ReaderParams;
use MWStake\MediaWiki\Component\Utils\Utility\GroupHelper;

class Reader extends \MWStake\MediaWiki\Component\CommonWebAPIs\Data\GroupStore\Reader {

	/** @var Title */
	protected $contextTitle;

	/**
	 * @param GroupHelper $groupHelper
	 * @param GlobalVarConfig $mwsgConfig
	 * @param Title $contextTitle
	 * @param HookContainer $hookContainer
	 */
	public function __construct(
		GroupHelper $groupHelper,
		GlobalVarConfig $mwsgConfig,
		Title $contextTitle,
		HookContainer $hookContainer
	) {
		parent::__construct( $groupHelper, $mwsgConfig, $hookContainer );
		$this->contextTitle = $contextTitle;
	}

	/**
	 *
	 * @param ReaderParams $params
	 * @return PrimaryDataProvider
	 */
	public function makePrimaryDataProvider( $params ) {
		return new PrimaryDataProvider(
			$this->groupHelper,
			$this->mwsgConfig,
			$this->contextTitle,
			$this->hookContainer
		);
	}

	/**
	 *
	 * @return null
	 */
	public function makeSecondaryDataProvider() {
		return null;
	}
}
