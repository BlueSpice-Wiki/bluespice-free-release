<?php

namespace MWStake\MediaWiki\Component\Events;

use MediaWiki\Message\Message;

class EventLink {
	/** @var string */
	private $url;
	/** @var Message */
	private $label;

	/**
	 * @param string $url
	 * @param Message $label
	 */
	public function __construct( string $url, Message $label ) {
		$this->url = $url;
		$this->label = $label;
	}

	/**
	 * @return string
	 */
	public function getUrl(): string {
		return $this->url;
	}

	/**
	 * @return Message
	 */
	public function getLabel(): Message {
		return $this->label;
	}
}
