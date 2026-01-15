<?php

namespace MWStake\MediaWiki\Component\Events;

interface ForcedEvent extends INotificationEvent {
	// Events implementing this interface cannot be opted out of
	// Consumers decide how to handle these events
}
