<?php

namespace BlueSpice\CountThings\Tag;

use MediaWiki\Parser\CoreParserFunctions;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;
use MWStake\MediaWiki\Component\GenericTagHandler\ITagHandler;

class CountUsersHandler implements ITagHandler {

	public function getRenderedContent( string $input, array $params, Parser $parser, PPFrame $frame ): string {
		$count = CoreParserFunctions::numberofusers( $parser );
		return " $count ";
	}
}
