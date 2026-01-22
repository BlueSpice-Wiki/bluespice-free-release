<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs\Data\UserQueryStore;

use MediaWiki\Config\Config;
use MediaWiki\Config\GlobalVarConfig;
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\TitleFactory;
use MediaWiki\User\UserFactory;
use MWStake\MediaWiki\Component\DataStore\ReaderParams;
use MWStake\MediaWiki\Component\Utils\UtilityFactory;
use Wikimedia\Rdbms\ILoadBalancer;

class Reader extends \MWStake\MediaWiki\Component\DataStore\Reader {
	/** @var ILoadBalancer */
	protected $lb;
	/** @var UserFactory */
	protected $userFactory;
	/** @var LinkRenderer */
	protected $linkRenderer;
	/** @var TitleFactory */
	protected $titleFactory;
	/** @var Config */
	protected $mwsgConfig;
	/** @var UtilityFactory */
	protected $utilityFactory;

	/**
	 * @param ILoadBalancer $lb
	 * @param UserFactory $userFactory
	 * @param LinkRenderer $linkRenderer
	 * @param TitleFactory $titleFactory
	 * @param GlobalVarConfig $mwsgConfig
	 * @param UtilityFactory|null $utilityFactory
	 */
	public function __construct(
		ILoadBalancer $lb, UserFactory $userFactory, LinkRenderer $linkRenderer, TitleFactory $titleFactory,
		GlobalVarConfig $mwsgConfig, ?UtilityFactory $utilityFactory = null
	) {
		parent::__construct();
		$this->lb = $lb;
		$this->userFactory = $userFactory;
		$this->linkRenderer = $linkRenderer;
		$this->titleFactory = $titleFactory;
		$this->mwsgConfig = $mwsgConfig;
		if ( !$utilityFactory ) {
			$utilityFactory = MediaWikiServices::getInstance()->getService( 'MWStakeCommonUtilsFactory' );
		}
		$this->utilityFactory = $utilityFactory;
	}

	/**
	 * @return UserSchema
	 */
	public function getSchema() {
		return new UserSchema();
	}

	/**
	 * @param ReaderParams $params
	 *
	 * @return PrimaryDataProvider
	 */
	public function makePrimaryDataProvider( $params ) {
		return new PrimaryDataProvider(
			$this->lb->getConnection( DB_REPLICA ), $this->getSchema(), $this->mwsgConfig, $this->utilityFactory
		);
	}

	/**
	 * @return SecondaryDataProvider
	 */
	public function makeSecondaryDataProvider() {
		return new SecondaryDataProvider(
			$this->userFactory, $this->linkRenderer, $this->titleFactory
		);
	}
}
