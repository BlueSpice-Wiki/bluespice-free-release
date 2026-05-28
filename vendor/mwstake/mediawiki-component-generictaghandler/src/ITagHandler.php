<?php

namespace MWStake\MediaWiki\Component\GenericTagHandler;

use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;

interface ITagHandler {

	/**
	 * @param string $input
	 * @param array $params
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return string
	 */
	public function getRenderedContent( string $input, array $params, Parser $parser, PPFrame $frame ): string;
}
