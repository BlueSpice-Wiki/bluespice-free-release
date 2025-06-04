<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\ContentDroplets\Droplet;

abstract class VariableDroplet extends MagicWordDroplet {

	/**
	 * @return string
	 */
	public function getContent(): string {
		$params = $this->getParams();

		$text = '{{' . strtoupper( $this->getMagicWord() );
		if ( $params ) {
			$text .= ":" . implode( '|', $params );
		}
		$text .= '}}';

		return $text;
	}

	/**
	 * Array of params for the variable
	 *
	 * @return array
	 */
	protected function getParams(): array {
		return [];
	}

}
