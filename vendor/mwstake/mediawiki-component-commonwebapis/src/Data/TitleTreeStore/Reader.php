<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs\Data\TitleTreeStore;

use MediaWiki\Language\Language;
use MediaWiki\Page\PageProps;
use MediaWiki\Title\NamespaceInfo;
use MediaWiki\Title\TitleFactory;
use MWStake\MediaWiki\Component\DataStore\ReaderParams;
use Wikimedia\Rdbms\ILoadBalancer;

class Reader extends \MWStake\MediaWiki\Component\CommonWebAPIs\Data\TitleQueryStore\Reader {

	/**
	 * @param ILoadBalancer $lb
	 * @param TitleFactory $titleFactory
	 * @param Language $language
	 * @param NamespaceInfo $nsInfo
	 * @param PageProps $pageProps
	 */
	public function __construct(
		ILoadBalancer $lb, TitleFactory $titleFactory, Language $language,
		NamespaceInfo $nsInfo, PageProps $pageProps
	) {
		parent::__construct( $lb, $titleFactory, $language, $nsInfo, $pageProps );
	}

	/**
	 * @return UserSchema
	 */
	public function getSchema() {
		return new TitleTreeSchema();
	}

	/**
	 * @param ReaderParams $params
	 *
	 * @return PrimaryDataProvider
	 */
	public function makePrimaryDataProvider( $params ) {
		return new PrimaryDataProvider(
			$this->lb->getConnection( DB_REPLICA ),
			$this->getSchema(),
			$this->language,
			$this->nsInfo
		);
	}

	/**
	 * @return SecondaryDataProvider
	 */
	public function makeSecondaryDataProvider() {
		return new SecondaryDataProvider( $this->titleFactory, $this->language, $this->pageProps );
	}
}
