<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

class BoolValueGet {

	/**
	 * @param mixed $value
	 * @return bool|null
	 */
	public static function from( mixed $value ): ?bool {
		if ( is_bool( $value ) ) {
			return $value;
		}
		if ( is_string( $value ) ) {
			$value = strtolower( $value );

			if ( $value === 'false' ) {
				return false;
			}
			if ( $value === 'true' ) {
				return true;
			}
			return null;
		}
		if ( is_int( $value ) ) {
			if ( $value === 0 ) {
				return false;
			}
			if ( $value === 1 ) {
				return true;
			}
			return null;
		}
		return null;
	}
}
