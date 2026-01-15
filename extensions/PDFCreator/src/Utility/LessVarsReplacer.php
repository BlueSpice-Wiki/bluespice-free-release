<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

use MWStake\MediaWiki\Component\CommonUserInterface\LessVars;

class LessVarsReplacer {

	/**
	 * @param string $style
	 * @return string
	 */
	public function replaceLessVars( string $style ): string {
		$lessVars = LessVars::getInstance();

		// Regex all Less vars and replace with value if exists
		return preg_replace_callback(
			'/@([a-zA-Z0-9_-]+)/',
			static function ( $matches ) use ( $lessVars ) {
				$var = $matches[1];
				$value = $lessVars->getVar( $var );
				if ( $value ) {
					return $value;
				}
				return $matches[0];
			},
			$style
		);
	}
}
