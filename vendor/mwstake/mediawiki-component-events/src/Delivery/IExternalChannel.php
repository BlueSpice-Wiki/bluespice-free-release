<?php

declare( strict_types=1 );

namespace MWStake\MediaWiki\Component\Events\Delivery;

use MWStake\MediaWiki\Component\Events\Notification;

interface IExternalChannel extends IChannel {
	/**
	 * In case of an error while delivering, throw an exception
	 * If channel is not ready to deliver , return false
	 * If channel delivered successfully, return true
	 *
	 * @param Notification $notification
	 * @return bool false if the notification should not be sent
	 */
	public function deliver( Notification $notification ): bool;
}
