<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs\Rest;

use MWStake\MediaWiki\Component\CommonWebAPIs\Data\CategoryQueryStore\Store;
use MWStake\MediaWiki\Component\DataStore\IStore;

class CategoryQueryStore extends TitleQueryStore {

	/**
	 * @return IStore
	 */
	protected function getStore(): IStore {
		return new Store( $this->lb, $this->titleFactory, $this->language, $this->nsInfo, $this->pageProps );
	}
}
