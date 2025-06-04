<?php

namespace MediaWiki\Extension\ContentDroplets\Droplet;

use MediaWiki\Message\Message;

class IconDroplet extends TemplateDroplet {

	/**
	 * Get target for the template
	 * @return string
	 */
	protected function getTarget(): string {
		return 'Icon';
	}

	/**
	 * Template params
	 * @return array
	 */
	protected function getParams(): array {
		return [
			1 => 'bi bi-clock'
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getName(): Message {
		return Message::newFromKey( 'contentdroplets-droplet-icon-name' );
	}

	/**
	 * @inheritDoc
	 */
	public function getDescription(): Message {
		return Message::newFromKey( 'contentdroplets-droplet-icon-description' );
	}

	/**
	 * @inheritDoc
	 */
	public function getIcon(): string {
		return 'droplet-icon';
	}

	/**
	 * @inheritDoc
	 */
	public function getRLModules(): array {
		return [ 'ext.contentdroplets.droplets.icon' ];
	}

	/**
	 * @inheritDoc
	 */
	public function getCategories(): array {
		return [ 'content', 'media' ];
	}
}
