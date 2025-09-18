<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\ContentDroplets\Droplet;

use MediaWiki\Extension\ContentDroplets\IDropletDescription;
use MediaWiki\Json\FormatJson;

/**
 * One can question how useful this is...
 */
abstract class ParserFunctionDroplet implements IDropletDescription {

	/**
	 * @return string
	 */
	public function getContent(): string {
		$function = $this->getFunction();
		$params = $this->getParams();

		return FormatJson::encode( [
			'target' => [
				'function' => $function,
				'wt' => "#$function:",
			],
			'params' => $params
		] );
	}

	/**
	 * Get target for the template
	 * @return string
	 */
	abstract protected function getFunction(): string;

	/**
	 * Main function param {{#getFunction()|mainParam}}
	 * @return string
	 */
	abstract protected function getMainParam(): string;

	/**
	 * Template params
	 * @return array
	 */
	abstract protected function getParams(): array;

	/**
	 * @return string|null
	 */
	public function getVeCommand(): ?string {
		return null;
	}
}
