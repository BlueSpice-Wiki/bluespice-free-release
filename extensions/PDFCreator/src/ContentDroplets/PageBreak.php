<?php

namespace MediaWiki\Extension\PDFCreator\ContentDroplets;

use MediaWiki\Extension\ContentDroplets\Droplet\TagDroplet;
use MediaWiki\Message\Message;

class PageBreak extends TagDroplet {

	/**
	 * @inheritDoc
	 */
	public function getName(): Message {
		return Message::newFromKey( 'pdfcreator-droplet-pagebreak-name' );
	}

	/**
	 * @inheritDoc
	 */
	public function getDescription(): Message {
		return Message::newFromKey( 'pdfcreator-droplet-pagebreak-description' );
	}

	/**
	 * @inheritDoc
	 */
	public function getIcon(): string {
		return 'droplet-pdf-pagebreak';
	}

	/**
	 * @inheritDoc
	 */
	public function getRLModules(): array {
		return [ 'ext.pdfcreator.droplets.styles' ];
	}

	/**
	 * @inheritDoc
	 */
	public function getCategories(): array {
		return [ 'export' ];
	}

	/**
	 *
	 * @inheritDoc
	 */
	protected function getTagName(): string {
		return 'pagebreak';
	}

	/**
	 * @inheritDoc
	 */
	protected function getAttributes(): array {
		return [];
	}

	/**
	 * @inheritDoc
	 */
	protected function hasContent(): bool {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function getVeCommand(): ?string {
		return 'pagebreakCommand';
	}
}
