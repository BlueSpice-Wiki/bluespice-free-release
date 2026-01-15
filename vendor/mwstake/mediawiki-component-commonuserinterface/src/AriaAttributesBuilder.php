<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

use MediaWiki\Parser\Sanitizer;

class AriaAttributesBuilder {

	/**
	 * @return AriaAttributesBuilder
	 */
	public static function factory() {
		return new static();
	}

	/**
	 * @param array $data
	 * @return array
	 */
	public function build( $data ): array {
		$attribs = [];

		foreach ( $data  as $key => $value ) {
			if ( $value === '' ) {
				continue;
			}
			$attrib = Sanitizer::safeEncodeTagAttributes( [
				"aria-$key" => $value
			] );
			$attrib = trim( $attrib );
			array_push( $attribs, $attrib );
		}

		return $attribs;
	}

	/**
	 * @param array $data
	 * @return string
	 */
	public function toString( $data ): string {
		$attribs = $this->build( $data );

		return implode( ' ', $attribs );
	}
}
