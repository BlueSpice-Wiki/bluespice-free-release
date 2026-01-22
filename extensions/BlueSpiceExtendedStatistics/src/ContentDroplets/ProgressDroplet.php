<?php

namespace BlueSpice\ExtendedStatistics\ContentDroplets;

use MediaWiki\Extension\ContentDroplets\Droplet\TagDroplet;
use MediaWiki\Message\Message;

class ProgressDroplet extends TagDroplet {

	/**
	 * @inheritDoc
	 */
	public function getName(): Message {
		return Message::newFromKey( 'bs-statistics-droplet-progress-name' );
	}

	/**
	 * @inheritDoc
	 */
	public function getDescription(): Message {
		return Message::newFromKey( 'bs-statistics-droplet-progress-description' );
	}

	/**
	 * @inheritDoc
	 */
	public function getIcon(): string {
		return 'droplet-progress';
	}

	/**
	 * @inheritDoc
	 */
	public function getRLModules(): array {
		return [ 'ext.bluespice.extendedstatistics.droplet' ];
	}

	/**
	 * @return array
	 */
	public function getCategories(): array {
		return [ 'content', 'visualization', 'data' ];
	}

	/**
	 *
	 * @return string
	 */
	protected function getTagName(): string {
		return 'bs:statistics:progress';
	}

	/**
	 * @return array
	 */
	protected function getAttributes(): array {
		return [
			'basecount' => 200,
			'baseitem' => '',
			'progressitem' => 'OK',
			'width' => 150
		];
	}

	/**
	 * @return bool
	 */
	protected function hasContent(): bool {
		return false;
	}

	/**
	 * @return string|null
	 */
	public function getVeCommand(): ?string {
		return 'bs_statistics_progressCommand';
	}

}
