<?php

namespace MediaWiki\Extension\MenuEditor;

use MediaWiki\Extension\MenuEditor\NodeProcessor\GenericKeywordNodeProcessor;
use MediaWiki\Extension\MenuEditor\NodeProcessor\KeywordNodeProcessor;
use MediaWiki\Extension\MenuEditor\NodeProcessor\RawTextNodeProcessor;
use MediaWiki\Extension\MenuEditor\NodeProcessor\TwoFoldLinkSpecNodeProcessor;
use MediaWiki\Extension\MenuEditor\NodeProcessor\WikiLinkNodeProcessor;

class Extension {

	public static function onRegistration() {
		mwsInitComponents();

		$GLOBALS['mwsgWikitextNodeProcessorRegistry'] += [
			'menu-raw-text' => [
				'class' => RawTextNodeProcessor::class
			],
			'menu-keyword' => [
				'class' => KeywordNodeProcessor::class
			],
			'menu-wiki-link' => [
				'class' => WikiLinkNodeProcessor::class,
				'services' => [ 'TitleFactory' ]
			],
			'menu-two-fold-link-spec' => [
				'class' => TwoFoldLinkSpecNodeProcessor::class,
				'services' => [ 'TitleFactory' ]
			],
			'menu-mediawiki-sidebar-keyword' => [
				'class' => GenericKeywordNodeProcessor::class,
				'args' => [ 'mediawiki-sidebar-keyword', [ 'TOOLBOX', 'LANGUAGES', 'SEARCH' ] ]
			]
		];
	}
}
