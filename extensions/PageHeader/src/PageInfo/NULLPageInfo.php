<?php

namespace PageHeader\PageInfo;

use MediaWiki\Context\IContextSource;
use MediaWiki\Language\RawMessage;
use MediaWiki\Message\Message;
use PageHeader\PageInfo;

class NULLPageInfo extends PageInfo {
	/**
	 *
	 * @return string
	 */
	public function getItemClass(): string {
		return 'null';
	}

	/**
	 *
	 * @return Message
	 */
	public function getLabelMessage(): Message {
		return new RawMessage( 'Invalid PageInfo' );
	}

	/**
	 *
	 * @return Message
	 */
	public function getTooltipMessage(): Message {
		return new RawMessage( 'Invalid PageInfo' );
	}

	/**
	 *
	 * @param IContextSource $context
	 * @return bool
	 */
	public function shouldShow( $context ): bool {
		return false;
	}

}
