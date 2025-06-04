<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\EnhancedUpload\ContentDroplets;

use MediaWiki\Extension\ContentDroplets\Droplet\TagDroplet;
use MediaWiki\Message\Message;

class AttachmentDroplet extends TagDroplet {

	/**
	 * @inheritDoc
	 */
	public function getName(): Message {
		return Message::newFromKey( 'enhancedupload-droplet-attachments-name' );
	}

	/**
	 * @inheritDoc
	 */
	public function getDescription(): Message {
		return Message::newFromKey( 'enhancedupload-droplet-attachments-description' );
	}

	/**
	 * @inheritDoc
	 */
	public function getIcon(): string {
		return 'droplet-attachments';
	}

	/**
	 * @inheritDoc
	 */
	public function getRLModules(): array {
		return [ 'ext.enhancedupload.attachments.bootstrap' ];
	}

	/**
	 * @return array
	 */
	public function getCategories(): array {
		return [ 'content', 'lists' ];
	}

	/**
	 *
	 * @return string
	 */
	protected function getTagName(): string {
		return 'attachments';
	}

	/**
	 * @return array
	 */
	protected function getAttributes(): array {
		return [
			'title',
			'prefix',
			'categories',
			'description',
			'skipoptions',
			'hideversion',
			'hideeditor',
			'showsize',
			'showcategories'
		];
	}

	/**
	 * @return bool
	 */
	protected function hasContent(): bool {
		return true;
	}

	/**
	 * @return string|null
	 */
	public function getVeCommand(): ?string {
		return 'attachmentCommand';
	}
}
