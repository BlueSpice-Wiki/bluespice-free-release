<?php

namespace MediaWiki\Extension\NotifyMe\Process;

use MediaWiki\Extension\NotifyMe\Channel\Email\DigestCreator;

class SendWeeklyDigest extends SendDigest {

	/**
	 * @return string
	 */
	protected function getTargetDigestPeriod(): string {
		return DigestCreator::DIGEST_TYPE_WEEKLY;
	}
}
