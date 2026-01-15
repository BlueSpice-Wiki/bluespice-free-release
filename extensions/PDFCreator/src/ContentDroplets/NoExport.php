<?php

namespace MediaWiki\Extension\PDFCreator\ContentDroplets;

use MediaWiki\Extension\ContentDroplets\Droplet\TagDroplet;
use MediaWiki\Message\Message;

class NoExport extends TagDroplet {

	/**
	 * @inheritDoc
	 */
	public function getName(): Message {
		return Message::newFromKey( 'pdfcreator-droplet-no-export-name' );
	}

	/**
	 * @inheritDoc
	 */
	public function getDescription(): Message {
		return Message::newFromKey( 'pdfcreator-droplet-no-export-description' );
	}

	/**
	 * @inheritDoc
	 */
	public function getIcon(): string {
		return 'droplet-pdf-no-export';
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
		return 'pdfexcludestart';
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
		return 'excludeExportCommand';
	}
}
