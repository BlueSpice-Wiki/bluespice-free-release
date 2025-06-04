<?php

namespace MediaWiki\Extension\UserProfile\Content;

use MediaWiki\Content\Content;
use MediaWiki\Content\JsonContentHandler;
use MediaWiki\Content\Renderer\ContentParseParams;
use MediaWiki\Extension\UserProfile\Action\EditProfileData;
use MediaWiki\Parser\ParserOutput;

class ProfileContentHandler extends JsonContentHandler {

	/**
	 * @param Content $content
	 * @param ContentParseParams $cpoParams
	 * @param ParserOutput &$parserOutput
	 * @return void
	 */
	protected function fillParserOutput(
		Content $content, ContentParseParams $cpoParams, ParserOutput &$parserOutput
	) {
		// Client-side implemenation
		$parserOutput->setRawText( '' );
	}

	/**
	 * @return string[]
	 */
	public function getActionOverrides() {
		return [
			'editprofiledata' => EditProfileData::class,
		];
	}
}
