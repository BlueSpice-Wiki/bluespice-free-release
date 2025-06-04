<?php

namespace MediaWiki\Extension\NotifyMe\MediaWiki\ForeignMail\Processor;

class LineBreak implements IProcessor {
	/**
	 * @inheritDoc
	 */
	public function process( string $text ): string {
		$this->removeBreaksInSentences( $text );

		return $text;
	}

	/**
	 * @param string &$text
	 */
	private function removeBreaksInSentences( string &$text ) {
		$text = $this->escapeHtmlTags( $text );
		$text = str_replace( "\n", '<br>', $text );
		$sentences = preg_split(
			'/([' . implode( '', static::DELIMITERS ) . '])/',
			$text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
		);
		foreach ( $sentences as &$sentence ) {
			$sentence = trim( $sentence );
			if ( in_array( trim( $sentence ), static::DELIMITERS ) ) {
				continue;
			}
			$isMatching = false;
			$sentence = preg_replace_callback( '/^([<br>]+)(.*)/', static function ( $matches ) use ( &$isMatching ){
				$isMatching = true;
				return $matches[1] . preg_replace( '/([^>])<br>([^<])/', '$1 $2', $matches[2] );
			}, $sentence );
			if ( !$isMatching ) {
				$sentence = preg_replace( '/([^>])<br>([^<])/', '$1 $2', $sentence );
			}
		}
		$text = implode( "", $sentences );
		$lines = explode( '<br>', $text );
		$lines = array_map( function ( $line ) {
			if ( trim( $line ) === '' ) {
				return '<p></p>';
			}
			return '<p>' . $this->fixWhitespaces( $line ) . '</p>';
		}, $lines );

		$text = implode( "\n", $lines );
		$text = $this->restoreHtmlTags( $text );
	}

	/**
	 * @param string $text
	 *
	 * @return string
	 */
	private function escapeHtmlTags( string $text ): string {
		// Does NOT support nested elements
		return preg_replace_callback(
			'/<[^>]+>.*?<\/[^>]+>/',
			static function ( $matches ) {
				return '<tag-' . base64_encode( $matches[0] ) . '>';
			},
			$text
		);
	}

	/**
	 * @param string $text
	 *
	 * @return string
	 */
	private function restoreHtmlTags( string $text ) {
		return preg_replace_callback(
			'/<tag-([^>]+)>/',
			static function ( $matches ) {
				return base64_decode( $matches[1] );
			},
			$text
		);
	}

	/**
	 * @param string $line
	 *
	 * @return string
	 */
	private function fixWhitespaces( $line ) {
		$res = preg_replace( '/\s+/', ' ', trim( $line ) );
		// Put whitespace after each delimiter after text continues
		return preg_replace(
			'/([' . implode( '', static::DELIMITERS ) . '])([^<\s0-9])/', '$1 $2', $res
		);
	}
}
