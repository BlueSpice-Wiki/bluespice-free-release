<?php

namespace MediaWiki\Extension\MenuEditor\NodeProcessor;

use MediaWiki\Extension\MenuEditor\IMenuNodeProcessor;

abstract class MenuNodeProcessor implements IMenuNodeProcessor {
	/** @var array|null */
	private $parsed = null;

	/**
	 * @param string $text
	 * @return int
	 */
	protected function getLevel( $text ): int {
		$parsed = $this->match( $text );
		return $parsed[1] ? strlen( $parsed[1] ) : 0;
	}

	/**
	 * @param string $text
	 * @return string
	 */
	protected function getNodeValue( $text ): string {
		$parsed = $this->match( $text );
		return trim( $parsed[2] );
	}

	/**
	 * @param string $text
	 * @return array
	 */
	private function match( $text ): array {
		$this->parsed = [];
		preg_match( '/^(\*{0,})(.*)$/', $text, $this->parsed );

		return $this->parsed;
	}
}
