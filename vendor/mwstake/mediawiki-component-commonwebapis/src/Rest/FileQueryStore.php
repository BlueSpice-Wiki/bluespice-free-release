<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs\Rest;

use MediaWiki\HookContainer\HookContainer;
use MediaWiki\Language\Language;
use MediaWiki\Page\PageProps;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Title\NamespaceInfo;
use MediaWiki\Title\TitleFactory;
use MWStake\MediaWiki\Component\CommonWebAPIs\Data\FileQueryStore\Store;
use MWStake\MediaWiki\Component\DataStore\IStore;
use Wikimedia\Rdbms\ILoadBalancer;

class FileQueryStore extends QueryStore {
	/** @var Store */
	private $store;

	/**
	 * @param HookContainer $hookContainer
	 * @param ILoadBalancer $lb
	 * @param TitleFactory $titleFactory
	 * @param Language $language
	 * @param NamespaceInfo $nsInfo
	 * @param PageProps $pageProps
	 * @param \RepoGroup $repoGroup
	 * @param PermissionManager|null $permissionManager
	 */
	public function __construct(
		HookContainer $hookContainer, ILoadBalancer $lb, TitleFactory $titleFactory,
		Language $language, NamespaceInfo $nsInfo, PageProps $pageProps, \RepoGroup $repoGroup,
		?PermissionManager $permissionManager = null
	) {
		parent::__construct( $hookContainer );
		if ( $permissionManager === null ) {
			$permissionManager = \MediaWiki\MediaWikiServices::getInstance()->getPermissionManager();
		}
		$this->store = new Store( $lb, $titleFactory, $language, $nsInfo, $pageProps,
			$repoGroup, $permissionManager );
	}

	/**
	 * @return IStore
	 */
	protected function getStore(): IStore {
		return $this->store;
	}
}
