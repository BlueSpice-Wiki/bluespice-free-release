<?php

namespace MWStake\MediaWiki\Component\Wire\Listener;

use MediaWiki\Page\PageIdentity;

interface WireListenerOnPage extends WireListener {

	/**
	 * Listen to messages on a page-specific channel. Null to receive messages for all pages.
	 * @return PageIdentity|null
	 */
	public function getPageToListenOn(): ?PageIdentity;
}
