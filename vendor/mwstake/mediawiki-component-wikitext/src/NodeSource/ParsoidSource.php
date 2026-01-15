<?php

namespace MWStake\MediaWiki\Component\Wikitext\NodeSource;

/**
 * Represents source data needed to process the node
 */
class ParsoidSource extends WikitextSource {
	/** @var \DOMNode */
	private $domNode;
	/** @var array */
	private $attributes;

	/**
	 * @param \DOMNode $node
	 * @param array $attributes
	 * @param string $wikitext
	 */
	public function __construct( \DOMNode $node, array $attributes, string $wikitext ) {
		parent::__construct( $wikitext );
		$this->domNode = $node;
		$this->attributes = $attributes;
	}

	/**
	 * @return \DOMNode
	 */
	public function getDOMNode(): \DOMNode {
		return $this->domNode;
	}

	/**
	 * @return array
	 */
	public function getAttributes(): array {
		return $this->attributes;
	}
}
