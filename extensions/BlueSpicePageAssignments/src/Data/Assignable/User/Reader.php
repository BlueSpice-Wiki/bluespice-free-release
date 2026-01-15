<?php

namespace BlueSpice\PageAssignments\Data\Assignable\User;

use BlueSpice\PageAssignments\Data\Assignable\User\ReaderParams as UserReaderParams;
use MediaWiki\Config\GlobalVarConfig;
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;
use MediaWiki\User\UserFactory;
use MWStake\MediaWiki\Component\DataStore\ReaderParams;
use Wikimedia\Rdbms\ILoadBalancer;

class Reader extends \MWStake\MediaWiki\Component\CommonWebAPIs\Data\UserQueryStore\Reader {

	/** @var Title */
	protected $contextTitle;

	/**
	 * @param ILoadBalancer $lb
	 * @param UserFactory $userFactory
	 * @param LinkRenderer $linkRenderer
	 * @param TitleFactory $titleFactory
	 * @param GlobalVarConfig $mwsgConfig
	 * @param Title $contextTitle
	 */
	public function __construct(
		ILoadBalancer $lb, UserFactory $userFactory, LinkRenderer $linkRenderer,
		TitleFactory $titleFactory, GlobalVarConfig $mwsgConfig, Title $contextTitle
	) {
		parent::__construct( $lb, $userFactory, $linkRenderer, $titleFactory, $mwsgConfig );
		$this->contextTitle = $contextTitle;
	}

	/**
	 *
	 * @param ReaderParams $params
	 * @return PrimaryDataProvider
	 */
	public function makePrimaryDataProvider( $params ) {
		return new PrimaryDataProvider(
			$this->lb->getConnection( DB_REPLICA ), $this->getSchema(), $this->mwsgConfig, $this->contextTitle );
	}

	/**
	 * @param ReaderParams $params
	 * @return \MWStake\MediaWiki\Component\DataStore\ResultSet
	 */
	public function read( $params ) {
		return parent::read( new UserReaderParams( $params ) );
	}

	/**
	 *
	 * @return null
	 */
	public function makeSecondaryDataProvider() {
		return new SecondaryDataProvider();
	}

}
