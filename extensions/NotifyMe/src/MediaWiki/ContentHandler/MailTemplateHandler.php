<?php

namespace MediaWiki\Extension\NotifyMe\MediaWiki\ContentHandler;

use MediaWiki\Content\TextContentHandler;
use MediaWiki\Extension\NotifyMe\MediaWiki\Action\EditMailTemplateAction;
use MediaWiki\Extension\NotifyMe\MediaWiki\Content\MailTemplate;

class MailTemplateHandler extends TextContentHandler {
	/**
	 * @param string|null $modelId
	 */
	public function __construct( $modelId = 'mail_template' ) {
		parent::__construct( $modelId, [ CONTENT_FORMAT_HTML ] );
	}

	/**
	 * @return string
	 */
	protected function getContentClass() {
		return MailTemplate::class;
	}

	/**
	 * @return false
	 */
	public function supportsSections() {
		return false;
	}

	/**
	 * @return bool
	 */
	public function supportsCategories() {
		return true;
	}

	/**
	 * @return false
	 */
	public function supportsRedirects() {
		return false;
	}

	/**
	 * @return string[]
	 */
	public function getActionOverrides() {
		return [
			'edit' => EditMailTemplateAction::class,
		];
	}
}
