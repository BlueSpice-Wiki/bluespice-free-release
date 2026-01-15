<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs\Rest;

use MediaWiki\Config\GlobalVarConfig;
use MediaWiki\HookContainer\HookContainer;
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\Rest\Response;
use MediaWiki\Title\TitleFactory;
use MediaWiki\User\UserFactory;
use MWStake\MediaWiki\Component\CommonWebAPIs\Data\UserQueryStore\Store;
use MWStake\MediaWiki\Component\DataStore\IStore;
use MWStake\MediaWiki\Component\DataStore\ResultSet;
use Wikimedia\Rdbms\ILoadBalancer;

class UserQueryStore extends QueryStore {
	/** @var Store */
	private $store;

	/**
	 * @param HookContainer $hookContainer
	 * @param ILoadBalancer $lb
	 * @param UserFactory $userFactory
	 * @param LinkRenderer $linkRenderer
	 * @param TitleFactory $titleFactory
	 * @param GlobalVarConfig $mwsgConfig
	 */
	public function __construct(
		HookContainer $hookContainer, ILoadBalancer $lb, UserFactory $userFactory,
		LinkRenderer $linkRenderer, TitleFactory $titleFactory, GlobalVarConfig $mwsgConfig
	) {
		parent::__construct( $hookContainer );
		$this->store = new Store( $lb, $userFactory, $linkRenderer, $titleFactory, $mwsgConfig );
	}

	/**
	 * @param ResultSet $result
	 *
	 * @return Response
	 */
	protected function returnResult( ResultSet $result ): Response {
		$this->hookContainer->run( 'MWStakeCommonWebAPIsQueryStoreResult', [ $this, &$result ] );
		$contentType = $contentType ?? 'application/json';
		$response = new Response( $this->encodeJson( [
			'buckets' => $this->getBuckets(),
			'results' => $result->getRecords(),
			'total' => $result->getTotal(),
		] ) );
		$response->setHeader( 'Content-Type', $contentType );
		return $response;
	}

	/**
	 * @return IStore
	 */
	protected function getStore(): IStore {
		return $this->store;
	}

	/**
	 * @return array
	 */
	private function getBuckets(): array {
		$groups = $this->getStore()
			->getReader()
			->makePrimaryDataProvider( $this->getReaderParams() )
			->getGroupBuckets();

		return [
			'groups' => $groups
		];
	}
}
