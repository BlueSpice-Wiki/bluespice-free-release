<?php

namespace MediaWiki\Extension\MenuEditor\NodeProcessor;

use MediaWiki\Extension\MenuEditor\Node\Keyword;
use MWStake\MediaWiki\Component\Wikitext\NodeSource\WikitextSource;
use MWStake\MediaWiki\Lib\Nodes\INode;
use MWStake\MediaWiki\Lib\Nodes\INodeSource;

class KeywordNodeProcessor extends MenuNodeProcessor {
	/**
	 * @param string $wikitext
	 * @return bool
	 */
	public function matches( $wikitext ): bool {
		$keywords = $this->getKeywords();
		return (bool)preg_match(
			'/^(\*{1,})\s{0,}(' . implode( '|', $keywords ) . ')$/',
			$wikitext
		);
	}

	/**
	 * @param INodeSource|WikitextSource $source
	 * @return INode
	 */
	public function getNode( INodeSource $source ): INode {
		return new Keyword(
			$this->getLevel( $source->getWikitext() ),
			$this->getNodeValue( $source->getWikitext() ),
			$source->getWikitext()
		);
	}

	/**
	 * @return string[]
	 */
	protected function getKeywords() {
		// TODO: Aint nice
		return [
			'SEARCH',
			'TOOLBOX',
			'LANGUAGES',
			'PAGESVISITED',
			'YOUREDITS'
		];
	}

	/**
	 * @inheritDoc
	 */
	public function supportsNodeType( $type ): bool {
		return str_contains( $type, 'menu-keyword' );
	}

	/**
	 * @param array $data
	 * @return INode
	 */
	public function getNodeFromData( array $data ): INode {
		return new Keyword(
			$data['level'],
			$data['keyword'],
			$data['wikitext'] ?? ''
		);
	}
}
