<?php

namespace MediaWiki\Extension\NotifyMe\Hook;

use MediaWiki\Extension\NotifyMe\ChannelFactory;

interface NotifyMeRegisterChannelHook {
	/**
	 * @param ChannelFactory $channelFactory
	 * @return void
	 */
	public function onNotifyMeRegisterChannel( ChannelFactory $channelFactory );

}
