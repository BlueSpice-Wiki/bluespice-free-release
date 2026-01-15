<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs\Rest;

use MediaWiki\HookContainer\HookContainer;
use MediaWiki\Language\Language;
use MediaWiki\Page\PageProps;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Title\NamespaceInfo;
use MediaWiki\Title\TitleFactory;
use MWStake\MediaWiki\Component\CommonWebAPIs\Data\TitleTreeStore\Store;
use MWStake\MediaWiki\Component\CommonWebAPIs\Data\TitleTreeStore\TitleTreeReaderParams;
use MWStake\MediaWiki\Component\DataStore\IStore;
use MWStake\MediaWiki\Component\DataStore\ReaderParams;
use Wikimedia\ParamValidator\ParamValidator;
use Wikimedia\Rdbms\ILoadBalancer;

class TitleTreeStore extends TitleQueryStore {

	/** @var PermissionManager */
	protected $permissionManager;

	/**
	 * @param HookContainer $hookContainer
	 * @param ILoadBalancer $lb
	 * @param TitleFactory $titleFactory
	 * @param Language $language
	 * @param NamespaceInfo $nsInfo
	 * @param PageProps $pageProps
	 * @param PermissionManager $permissionManager
	 */
	public function __construct(
		HookContainer $hookContainer, ILoadBalancer $lb, TitleFactory $titleFactory,
		Language $language, NamespaceInfo $nsInfo, PageProps $pageProps, PermissionManager $permissionManager
	) {
		parent::__construct( $hookContainer, $lb, $titleFactory, $language, $nsInfo, $pageProps );
		$this->permissionManager = $permissionManager;
	}

	/**
	 * @return IStore
	 */
	protected function getStore(): IStore {
		return new Store(
			$this->lb,
			$this->titleFactory,
			$this->language,
			$this->nsInfo,
			$this->pageProps,
			$this->permissionManager
		);
	}

	/**
	 * @return ReaderParams
	 */
	protected function getReaderParams(): ReaderParams {
		return new TitleTreeReaderParams( [
			'query' => $this->getQuery(),
			'start' => $this->getOffset(),
			'limit' => $this->getLimit(),
			'filter' => $this->getFilter(),
			'sort' => $this->getSort(),
			'node' => $this->getValidatedParams()['node'] ?? '',
			'expand-paths' => $this->getValidatedParams()['expand-paths'] ?? [],
		] );
	}

	/**
	 * @return array[]
	 */
	protected function getStoreSpecificParams(): array {
		return [
			'node' => [
				static::PARAM_SOURCE => 'query',
				ParamValidator::PARAM_REQUIRED => false,
				ParamValidator::PARAM_TYPE => 'string',
			],
			'expand-paths' => [
				static::PARAM_SOURCE => 'query',
				ParamValidator::PARAM_REQUIRED => false,
				ParamValidator::PARAM_TYPE => 'string',
			],
		];
	}
}
