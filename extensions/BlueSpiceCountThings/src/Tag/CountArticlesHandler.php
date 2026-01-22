<?php

namespace BlueSpice\CountThings\Tag;

use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;
use MediaWiki\SiteStats\SiteStats;
use MediaWiki\Title\NamespaceInfo;
use MWStake\MediaWiki\Component\GenericTagHandler\ITagHandler;

class CountArticlesHandler implements ITagHandler {

	/**
	 * @param NamespaceInfo $namespaceInfo
	 */
	public function __construct(
		private readonly NamespaceInfo $namespaceInfo
	) {
	}

	public function getRenderedContent( string $input, array $params, Parser $parser, PPFrame $frame ): string {
		$count = 0;
		$namespaces = $this->namespaceInfo->getValidNamespaces();
		foreach ( $namespaces as $namespace ) {
			if ( $this->namespaceInfo->isContent( $namespace ) ) {
				$count += SiteStats::pagesInNs( $namespace );
			}
		}
		return " $count ";
	}
}
