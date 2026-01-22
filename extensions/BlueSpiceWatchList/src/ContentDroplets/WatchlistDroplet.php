<?php

namespace BlueSpice\WatchList\ContentDroplets;

use MediaWiki\Extension\ContentDroplets\Droplet\TagDroplet;
use MediaWiki\Message\Message;

class WatchlistDroplet extends TagDroplet {

	/**
	 * @inheritDoc
	 */
	public function getName(): Message {
		return Message::newFromKey( 'bs-watchlist-droplet-name' );
	}

	/**
	 * @inheritDoc
	 */
	public function getDescription(): Message {
		return Message::newFromKey( 'bs-watchlist-droplet-description' );
	}

	/**
	 * @inheritDoc
	 */
	public function getIcon(): string {
		return 'droplet-watchlist';
	}

	/**
	 * @inheritDoc
	 */
	public function getRLModules(): array {
		return [ 'ext.bluespice.watchlist.droplet' ];
	}

	/**
	 * @return array
	 */
	public function getCategories(): array {
		return [ 'lists' ];
	}

	/**
	 *
	 * @return string
	 */
	protected function getTagName(): string {
		return 'bs:watchlist';
	}

	/**
	 * @return array
	 */
	protected function getAttributes(): array {
		return [
			'count' => '1',
			'maxtitlelength' => '5',
			'order' => '',
			'pagename' => '',
			'time' => ''
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
		return 'watchlistCommand';
	}

}
