<?php

namespace MediaWiki\Extension\NotifyMe\MediaWiki\Content;

use MediaWiki\Content\TextContent;

class MailTemplate extends TextContent {

	/**
	 * @param string $text
	 */
	public function __construct( $text ) {
		parent::__construct( $text, 'mail_template' );
	}
}
