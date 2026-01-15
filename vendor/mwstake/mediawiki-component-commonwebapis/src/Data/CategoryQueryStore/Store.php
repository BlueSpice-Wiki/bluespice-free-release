<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs\Data\CategoryQueryStore;

use MWStake\MediaWiki\Component\CommonWebAPIs\Data\TitleQueryStore\Store as TitleQueryStore;

class Store extends TitleQueryStore {

	/**
	 * @return Reader
	 */
	public function getReader() {
		return new Reader(
			$this->lb, $this->titleFactory, $this->language, $this->nsInfo, $this->pageProps
		);
	}
}
