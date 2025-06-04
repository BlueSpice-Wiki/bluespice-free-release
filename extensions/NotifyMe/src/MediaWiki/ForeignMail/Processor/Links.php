<?php

namespace MediaWiki\Extension\NotifyMe\MediaWiki\ForeignMail\Processor;

class Links implements IProcessor {
	/**
	 * @inheritDoc
	 */
	public function process( string $text ): string {
		// [my link](http://example.com)
		$this->replaceSimpleLinks( $text );
		// plain URL (http://example.com)
		$this->replaceRawUrl( $text );

		return $text;
	}

	/**
	 * @param string &$text
	 */
	private function replaceSimpleLinks( string &$text ) {
		$text = preg_replace_callback(
			'/([^\s<>]+?[' . implode( '', static::DELIMITERS ) . ']?)\s*\(<(http[^\s>]+)>\)/',
			static function ( $matches ) {
				$label = $matches[1];
				$url = $matches[2];
				return "<a href=\"$url\">$label</a>";
			},
			$text
		);
	}

	/**
	 * @param string &$text
	 */
	private function replaceRawUrl( string &$text ) {
		$text = preg_replace_callback(
			'/(?<!<a href=")(http[^\s>]+)(?!<\/a>)/',
			static function ( $matches ) {
				$url = $matches[0];
				return "<a href=\"$url\">$url</a>";
			},
			$text
		);
	}
}
