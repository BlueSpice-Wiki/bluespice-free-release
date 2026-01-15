<?php

namespace MWStake\MediaWiki\Component\Events;

use MediaWiki\Title\Title;

interface ITitleEvent extends INotificationEvent {
	/**
	 * @return Title
	 */
	public function getTitle(): Title;
}
