<?php

namespace BlueSpice\Discovery;

interface IAttentionIndicator {
	/**
	 * @return string
	 */
	public function getKey(): string;

	/**
	 * @return bool
	 */
	public function hasIndication(): bool;

	/**
	 * @return int
	 */
	public function getIndicationCount(): int;
}
