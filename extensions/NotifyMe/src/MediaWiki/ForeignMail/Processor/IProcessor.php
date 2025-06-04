<?php

namespace MediaWiki\Extension\NotifyMe\MediaWiki\ForeignMail\Processor;

interface IProcessor {
	public const DELIMITERS = [ '.', '!', '?', ':' ];

	/**
	 * @param string $text
	 * @return string
	 */
	public function process( string $text ): string;
}
