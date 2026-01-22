<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\ContentDroplets\Droplet;

use MediaWiki\Extension\ContentDroplets\IDropletDescription;
use MediaWiki\Json\FormatJson;

/**
 * One can question how useful this is...
 */
abstract class TemplateDroplet implements IDropletDescription {

	/**
	 * @return string
	 */
	public function getContent(): string {
		$target = $this->getTarget();
		$params = $this->getParams();
		$params = $this->formatParams( $params );

		return FormatJson::encode( [
			'target' => [
				'href' => "./Template:$target",
				'wt' => $target
			],
			'params' => $params
		] );
	}

	/**
	 * Get target for the template
	 * @return string
	 */
	abstract protected function getTarget(): string;

	/**
	 * Template params
	 * @return array
	 */
	abstract protected function getParams(): array;

	/**
	 * @param array $params
	 * @return array
	 */
	private function formatParams( array $params ) {
		if ( $this->isAssoc( $params ) ) {
			return $params;
		}
		$res = [];
		foreach ( $params as $index => $param ) {
			$res[$index + 1] = $param;
		}
		return $res;
	}

	/**
	 * Check if array is associative
	 * @param array $params
	 * @return bool
	 */
	private function isAssoc( array $params ) {
		return array_keys( $params ) !== range( 0, count( $params ) - 1 );
	}

	/**
	 * @inheritDoc
	 */
	public function getVeCommand(): ?string {
		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function listDroplet(): bool {
		return true;
	}
}
