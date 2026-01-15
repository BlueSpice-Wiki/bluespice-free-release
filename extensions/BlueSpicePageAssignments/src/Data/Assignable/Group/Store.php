<?php

namespace BlueSpice\PageAssignments\Data\Assignable\Group;

use MediaWiki\Config\GlobalVarConfig;
use MediaWiki\Title\Title;
use MWStake\MediaWiki\Component\Utils\UtilityFactory;

class Store extends \MWStake\MediaWiki\Component\CommonWebAPIs\Data\GroupStore\Store {

	/** @var Title */
	protected $contextTitle;

	/**
	 * @param UtilityFactory $utilityFactory
	 * @param GlobalVarConfig $mwsgConfig
	 * @param Title $contextTitle
	 */
	public function __construct( UtilityFactory $utilityFactory, GlobalVarConfig $mwsgConfig, Title $contextTitle ) {
		parent::__construct( $utilityFactory, $mwsgConfig );
		$this->contextTitle = $contextTitle;
	}

	/**
	 *
	 * @return Reader
	 */
	public function getReader() {
		return new Reader( $this->groupHelper, $this->mwsgConfig, $this->contextTitle );
	}

}
