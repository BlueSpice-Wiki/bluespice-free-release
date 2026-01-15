<?php

namespace MWStake\MediaWiki\Component\Wikitext\NodeSource;

use MWStake\MediaWiki\Lib\Nodes\INodeSource;

/**
 * Represents source data needed to process the node
 */
class WikitextSource implements INodeSource {
	/** @var string */
	private $wikitext;

	/**
	 * WikitextSource constructor.
	 * @param string $wikitext
	 */
	public function __construct( string $wikitext ) {
		$this->wikitext = $wikitext;
	}

	/**
	 * @return string
	 */
	public function getWikitext(): string {
		return $this->wikitext;
	}
}
