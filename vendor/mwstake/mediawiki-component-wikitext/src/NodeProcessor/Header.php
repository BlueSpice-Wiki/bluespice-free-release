<?php

namespace MWStake\MediaWiki\Component\Wikitext\NodeProcessor;

use MWStake\MediaWiki\Component\Wikitext\IParsoidNodeProcessor;
use MWStake\MediaWiki\Component\Wikitext\NodeSource\ParsoidSource;
use MWStake\MediaWiki\Lib\Nodes\INode;
use MWStake\MediaWiki\Lib\Nodes\INodeSource;

class Header implements IParsoidNodeProcessor {
	/**
	 * @inheritDoc
	 */
	public function matchTag(): array {
		return [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ];
	}

	/**
	 * @inheritDoc
	 */
	public function supportsNodeType( $type ): bool {
		return $type === 'header';
	}

	/**
	 * @inheritDoc
	 */
	public function matchAttributes(): array {
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function matchCallback( \DOMNode $domNode, $attributes ): ?bool {
		return null;
	}

	/**
	 * @param INodeSource|ParsoidSource $source
	 * @return INode
	 */
	public function getNode( INodeSource $source ): INode {
		return new \MWStake\MediaWiki\Component\Wikitext\Node\Header(
			$this->extractLevelFromNode( $source->getDOMNode() ),
			$source->getDOMNode(),
			$wikitext
		);
	}

	/**
	 * @param array $data
	 * @return INode
	 */
	public function getNodeFromData( array $data ): INode {
		throw new \BadMethodCallException( 'Not implemented' );
	}

	/**
	 * @param \DOMNode $node
	 * @return mixed
	 */
	private function extractLevelFromNode( \DOMNode $node ) {
		return (int)substr( $node->nodeName, -1 );
	}
}
