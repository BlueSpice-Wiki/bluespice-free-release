<?php

namespace BlueSpice\CountThings\Tag;

use MediaWiki\MediaWikiServices;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;

class CountArticles extends \BlueSpice\Tag\Tag {

	/**
	 *
	 * @return bool
	 */
	public function needsDisabledParserCache() {
		return true;
	}

	/**
	 *
	 * @param string $processedInput
	 * @param array $processedArgs
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return CountArticlesHandler
	 */
	public function getHandler( $processedInput, array $processedArgs, Parser $parser,
		PPFrame $frame ) {
		$namespaceInfo = MediaWikiServices::getInstance()->getNamespaceInfo();
		return new CountArticlesHandler(
			$processedInput,
			$processedArgs,
			$parser,
			$frame,
			$namespaceInfo
		);
	}

	/**
	 *
	 * @return string[]
	 */
	public function getTagNames() {
		return [
			'bs:countarticles'
		];
	}

	/**
	 *
	 * @return string
	 */
	public function getContainerElementName() {
		return 'span';
	}
}
