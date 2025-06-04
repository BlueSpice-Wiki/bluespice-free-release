<?php

namespace MediaWiki\Extension\MenuEditor\NodeProcessor;

use MediaWiki\Extension\MenuEditor\Node\GenericKeyword;
use MWStake\MediaWiki\Component\Wikitext\NodeSource\WikitextSource;
use MWStake\MediaWiki\Lib\Nodes\INode;
use MWStake\MediaWiki\Lib\Nodes\INodeSource;

class GenericKeywordNodeProcessor extends KeywordNodeProcessor {

	/**
	 *
	 * @var string
	 */
	private $type = '';

	/**
	 *
	 * @var array
	 */
	private $keywords = [];

	/**
	 * @param string $type
	 * @param array $keywords
	 */
	public function __construct( $type, $keywords ) {
		$this->type = $type;
		$this->keywords = $keywords;
	}

	/**
	 * @param string $wikitext
	 * @return bool
	 */
	public function matches( $wikitext ): bool {
		return (bool)preg_match(
			'/^(\*{1,})\s{0,}(' . implode( '|', $this->keywords ) . ')$/',
			$wikitext
		);
	}

	/**
	 * @param INodeSource|WikitextSource $source
	 * @return INode
	 */
	public function getNode( INodeSource $source ): INode {
		$node = new GenericKeyword(
			$this->type,
			$this->getLevel( $source->getWikitext() ),
			$this->getNodeValue( $source->getWikitext() ),
			$source->getWikitext()
		);
		return $node;
	}

	/**
	 * @param array $data
	 * @return INode
	 */
	public function getNodeFromData( array $data ): INode {
		return new GenericKeyword(
			$this->type,
			$data['level'],
			$data['keyword'],
			$data['wikitext'] ?? ''
		);
	}

	/**
	 * @inheritDoc
	 */
	public function supportsNodeType( $type ): bool {
		return $type === $this->type;
	}

}
