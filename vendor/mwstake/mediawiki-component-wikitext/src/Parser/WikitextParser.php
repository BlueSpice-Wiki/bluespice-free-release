<?php

namespace MWStake\MediaWiki\Component\Wikitext\Parser;

use MediaWiki\Parser\Parsoid\Config\DataAccess;
use MediaWiki\Parser\Parsoid\Config\PageConfig;
use MediaWiki\Parser\Parsoid\Config\SiteConfig;
use MediaWiki\Revision\RevisionRecord;
use MWStake\MediaWiki\Component\Wikitext\IParsoidNodeProcessor;
use MWStake\MediaWiki\Component\Wikitext\NodeSource\ParsoidSource;
use MWStake\MediaWiki\Lib\Nodes\INode;
use MWStake\MediaWiki\Lib\Nodes\INodeProcessor;
use MWStake\MediaWiki\Lib\Nodes\IParser;
use Wikimedia\Parsoid\Parsoid;

class WikitextParser extends MutableWikitextParser implements IParser {
	/** @var Parsoid */
	private $parsoid;
	/** @var PageConfig */
	private $pageConfig;
	/** @var INodeProcessor[] */
	private $nodeProcessors;
	/** @var INode[] */
	private $nodes = [];
	/** @var \DOMDocument|null */
	private $dom = null;

	/**
	 * @param RevisionRecord $revision
	 * @param INodeProcessor[] $nodeProcessors
	 * @param SiteConfig $siteConfig
	 * @param DataAccess $dataAccess
	 * @param PageConfig $pageConfig
	 */
	public function __construct(
		RevisionRecord $revision, $nodeProcessors, SiteConfig $siteConfig,
		DataAccess $dataAccess, PageConfig $pageConfig
	) {
		parent::__construct( $revision );
		$this->parsoid = new Parsoid( $siteConfig, $dataAccess );
		$this->pageConfig = $pageConfig;
		$this->nodeProcessors = $nodeProcessors;
	}

	/**
	 * @return INode[]
	 */
	public function parse(): array {
		// Convert to HTML. This does:
		// - tokenizes the document, reliably parsing different nodes
		// - extracts all important info (like params for template, attributes of img...)
		// - allows us to cast to DOMNode to easily acess those attributes
		$data = $this->parsoid->wikitext2html( $this->pageConfig, [
			'pageBundle' => true, 'body_only' => true, 'wrapSections' => false
		] );
		// There might be sligh differences between Parsoid-parsed WT and raw WT
		// Convert back to WT and consider that the source text of the page
		// This ensures that mutating the page will reliably replace nodes
		$this->setRawData( $this->parsoidHtmlToWikitext( $data->html ) );

		$this->dom = new \DOMDocument();
		// DOMDocument does not like HTML5 tags (it loads them fine, just complains)
		libxml_use_internal_errors( true );
		$this->dom->loadHTML( $data->html );
		libxml_clear_errors();
		$this->processDOMNode( $this->dom );

		return $this->nodes;
	}

	/**
	 * Process DOMNode and create INode if possible
	 * @param \DOMNode $dom
	 * @param bool|null $nodeType Only process given nodeType (INodeProcessor key)
	 */
	private function processDOMNode( \DOMNode $dom, $nodeType = null ) {
		foreach ( $dom->childNodes as $node ) {
			$attributes = $this->extractNodeAttributes( $node );
			$this->possiblyAddNode( $node, $attributes, $nodeType );
			if ( $node->hasChildNodes() ) {
				$this->processDOMNode( $node, $nodeType );
			}
		}
	}

	/**
	 * Analyze DOMnode and check if any of the processors supports it
	 *
	 * @param \DOMNode $node
	 * @param array $attributes
	 * @param bool|null $nodeType Only process given nodeType (INodeProcessor key)
	 * @throws \Exception
	 */
	private function possiblyAddNode( \DOMNode $node, $attributes, $nodeType = null ) {
		/**
		 * @var string $key
		 * @var INodeProcessor $processor
		 */
		foreach ( $this->nodeProcessors as $key => $processor ) {
			if ( $nodeType && $nodeType !== $key ) {
				// Not requested
				continue;
			}
			if ( !$processor instanceof IParsoidNodeProcessor ) {
				continue;
			}
			$matches = $processor->matchCallback( $node, $attributes );
			if ( $matches === null ) {
				if ( !in_array( $node->nodeName, $processor->matchTag() ) ) {
					continue;
				}
				foreach ( $processor->matchAttributes() as $matchKey => $value ) {
					if ( !isset( $attributes[$matchKey] ) ) {
						continue 2;
					}
					if ( $value !== '*' && $attributes[$matchKey] !== $value ) {
						continue 2;
					}
				}
				$matches = true;
			}

			if ( !$matches ) {
				continue;
			}

			// Get WT for the node
			$wikitext = $this->parsoidHtmlToWikitext( $this->dom->saveHTML( $node ) );
			$source = new ParsoidSource( $node, $attributes, $wikitext );
			$this->nodes[] = $processor->getNode( $source );
		}
	}

	/**
	 * @param \DOMNode $node
	 * @return array
	 */
	private function extractNodeAttributes( \DOMNode $node ) {
		$attributes = [];
		$nodeMap = $node->attributes;
		if ( $nodeMap === null ) {
			return [];
		}
		for ( $i = 0; $i < $nodeMap->count(); $i++ ) {
			$attributes[$nodeMap->item( $i )->nodeName] = $nodeMap->item( $i )->nodeValue;
		}

		return $attributes;
	}

	/**
	 * @param string $html
	 * @return string
	 */
	private function parsoidHtmlToWikitext( $html ) {
		return $this->parsoid->html2wikitext( $this->pageConfig, $html );
	}
}
