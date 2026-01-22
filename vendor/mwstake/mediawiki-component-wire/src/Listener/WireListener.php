<?php

namespace MWStake\MediaWiki\Component\Wire\Listener;

use MWStake\MediaWiki\Component\Wire\WireMessage;

/**
 * Listens on messages on all channel
 */
interface WireListener {

	/**
	 * @param WireMessage $message
	 * @return mixed
	 */
	public function onWireMessage( WireMessage $message );
}
