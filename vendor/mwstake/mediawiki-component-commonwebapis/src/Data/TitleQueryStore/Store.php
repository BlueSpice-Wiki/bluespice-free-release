<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs\Data\TitleQueryStore;

use MediaWiki\Language\Language;
use MediaWiki\Page\PageProps;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Title\NamespaceInfo;
use MediaWiki\Title\TitleFactory;
use MWStake\MediaWiki\Component\DataStore\IStore;
use Wikimedia\Rdbms\ILoadBalancer;

/*
 * @stable to extend
 */
class Store implements IStore {
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
	/** @var PermissionManager */
	protected $permissionManager;

	/**
	 * @param ILoadBalancer $lb
	 * @param TitleFactory $titleFactory
	 * @param Language $language
	 * @param NamespaceInfo $nsInfo
	 * @param PageProps $pageProps
	 * @param PermissionManager|null $permissionManager
	 */
	public function __construct(
		ILoadBalancer $lb, TitleFactory $titleFactory, Language $language,
		NamespaceInfo $nsInfo, PageProps $pageProps, ?PermissionManager $permissionManager = null
	) {
		$this->lb = $lb;
		$this->titleFactory = $titleFactory;
		$this->language = $language;
		$this->nsInfo = $nsInfo;
		$this->pageProps = $pageProps;
		$this->permissionManager = $permissionManager;
		if ( $this->permissionManager === null ) {
			$this->permissionManager = \MediaWiki\MediaWikiServices::getInstance()->getPermissionManager();
		}
	}

	/**
	 * @return UserSchema
	 */
	public function getSchema() {
		return new TitleSchema();
	}

	/**
	 * @return Reader
	 */
	public function getReader() {
		return new Reader(
			$this->lb, $this->titleFactory, $this->language, $this->nsInfo,
			$this->pageProps, $this->permissionManager
		);
	}

	/**
	 * @return PrimaryDataProvider
	 */
	public function getWriter() {
		throw new NotImplementedException();
	}
}
