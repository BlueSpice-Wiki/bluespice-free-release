<?php

namespace MediaWiki\Extension\NotifyMe\Event;

/**
 * @deprecated Use MWStake\MediaWiki\Component\Events\NotifyAgentEvent instead
 */
interface NotifyAgentEvent extends \MWStake\MediaWiki\Component\Events\NotifyAgentEvent {
	// Events using this interface will generate a notification for the agent
}
