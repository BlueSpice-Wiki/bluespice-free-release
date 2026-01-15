<?php

namespace BlueSpice\Discovery\BreadcrumbDataProvider;

use MediaWiki\Title\Title;

class PagesWithoutSubpagesProvider extends BaseBreadcrumbDataProvider {

	/**
	 * @param Title $title
	 * @return array
	 */
	public function getNodes( Title $title ): array {
		$nodes[] = [
			'id' => $title->getArticleID(),
			'nodeText' => $title->getText(),
			'url' => $title->getLocalURL(),
			'classes' => [ 'active' ],
			'current' => true,
			'title' => $title->getFullText(),
			'splitBtnClass' => 'd-none',
			'subpages' => false,
			'path' => ''
		];

		return $nodes;
	}

	/**
	 *
	 * @param Title $title
	 * @return bool
	 */
	public function applies( Title $title ): bool {
		$namespaceIndex = $title->getNamespace();

		if ( !$this->namespaceInfo->hasSubpages( $namespaceIndex ) && !$title->isSpecialPage() ) {
			return true;
		}
		return false;
	}
}
