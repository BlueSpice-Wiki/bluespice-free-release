<?php

namespace MWStake\MediaWiki\Component\Events;

interface PriorityEvent extends INotificationEvent {
	// Events implementing this interface will be prioritized over other events
}
