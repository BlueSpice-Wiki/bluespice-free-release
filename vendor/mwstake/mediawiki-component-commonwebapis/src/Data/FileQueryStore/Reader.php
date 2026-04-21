<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs\Data\FileQueryStore;

use MediaWiki\Language\Language;
use MediaWiki\Page\PageProps;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Title\NamespaceInfo;
use MediaWiki\Title\TitleFactory;
use MWStake\MediaWiki\Component\DataStore\ReaderParams;
use Wikimedia\Rdbms\ILoadBalancer;

class Reader extends \MWStake\MediaWiki\Component\DataStore\Reader {
	/** @var ILoadBalancer */
	protected $lb;
	/** @var TitleFactory */
	protected $titleFactory;
	/** @var Language */
	protected $language;
	/** @var NamespaceInfo */
	protected $nsInfo;
	/** @var PageProps */
	protected $pageProps;
	/** @var \RepoGroup */
	protected $repoGroup;
	/** @var PermissionManager */
	protected $permissionManager;

	/**
	 * @param ILoadBalancer $lb
	 * @param TitleFactory $titleFactory
	 * @param Language $language
	 * @param NamespaceInfo $nsInfo
	 * @param PageProps $pageProps
	 * @param \RepoGroup $repoGroup
	 * @param PermissionManager|null $permissionManager
	 */
	public function __construct(
		ILoadBalancer $lb, TitleFactory $titleFactory, Language $language,
		NamespaceInfo $nsInfo, PageProps $pageProps, \RepoGroup $repoGroup,
		?PermissionManager $permissionManager = null
	) {
		parent::__construct();
		$this->lb = $lb;
		$this->titleFactory = $titleFactory;
		$this->language = $language;
		$this->nsInfo = $nsInfo;
		$this->pageProps = $pageProps;
		$this->repoGroup = $repoGroup;
		$this->permissionManager = $permissionManager;
		if ( $this->permissionManager === null ) {
			$this->permissionManager = \MediaWiki\MediaWikiServices::getInstance()->getPermissionManager();
		}
	}

	/**
	 * @return FileSchema
	 */
	public function getSchema() {
		return new FileSchema();
	}

	/**
	 * @param ReaderParams $params
	 *
	 * @return PrimaryDataProvider
	 */
	public function makePrimaryDataProvider( $params ) {
		return new PrimaryDataProvider(
			$this->lb->getConnection( DB_REPLICA ), $this->getSchema(), $this->language,
			$this->nsInfo, $this->permissionManager
		);
	}

	/**
	 * @return SecondaryDataProvider
	 */
	public function makeSecondaryDataProvider() {
		return new SecondaryDataProvider( $this->titleFactory, $this->language,
			$this->pageProps, $this->repoGroup );
	}
}
