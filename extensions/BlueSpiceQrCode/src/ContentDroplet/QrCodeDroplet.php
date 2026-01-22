<?php

namespace BlueSpice\QrCode\ContentDroplet;

use MediaWiki\Extension\ContentDroplets\Droplet\TagDroplet;
use MediaWiki\Message\Message;

class QrCodeDroplet extends TagDroplet {

	/**
	 * @inheritDoc
	 */
	public function getName(): Message {
		return Message::newFromKey( 'bs-qr-code-droplet-name' );
	}

	/**
	 * @inheritDoc
	 */
	public function getDescription(): Message {
		return Message::newFromKey( 'bs-qr-code-droplet-desc' );
	}

	/**
	 * @inheritDoc
	 */
	public function getTagName(): string {
		return 'qrcode';
	}

	/**
	 * @inheritDoc
	 */
	public function getIcon(): string {
		return 'droplet-qrcode';
	}

	/**
	 * @inheritDoc
	 */
	public function getRLModules(): array {
		return [ 'ext.bluespice.qrCode.droplet' ];
	}

	/**
	 * @inheritDoc
	 */
	public function getCategories(): array {
		return [ 'content', 'navigation' ];
	}

	/**
	 * @inheritDoc
	 */
	protected function getAttributes(): array {
		return [
			'page',
			'query',
			'desc',
			'size'
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function hasContent(): bool {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function getVeCommand(): ?string {
		return 'qrcodeCommand';
	}
}
