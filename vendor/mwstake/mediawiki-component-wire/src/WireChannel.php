<?php

namespace MWStake\MediaWiki\Component\Wire;

use Stringable;

class WireChannel implements Stringable {

	/**
	 * @param string $key
	 */
	public function __construct(
		private readonly string $key
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function __toString(): string {
		return $this->key;
	}

	/**
	 * @return string
	 */
	public function getKey(): string {
		return $this->key;
	}
}
