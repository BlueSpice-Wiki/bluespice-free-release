<?php

namespace MediaWiki\Extension\MenuEditor\Parser;

use MediaWiki\Extension\MenuEditor\IMenuNodeProcessor;
use MediaWiki\Extension\MenuEditor\Node\MenuNode;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRecord;
use MWException;
use MWStake\MediaWiki\Component\Wikitext\NodeSource\WikitextSource;
use MWStake\MediaWiki\Component\Wikitext\Parser\MutableWikitextParser;
use MWStake\MediaWiki\Lib\Nodes\INode;
use MWStake\MediaWiki\Lib\Nodes\INodeProcessor;
use MWStake\MediaWiki\Lib\Nodes\IParser;

class WikitextMenuParser extends MutableWikitextParser implements IParser, IMenuParser {
	/** @var INodeProcessor[] */
	private $nodeProcessors;
	/** @var INode[] */
	private $nodes = [];

	/**
	 * @param RevisionRecord $revision
	 * @param array $nodeProcessors
	 */
	public function __construct(
		RevisionRecord $revision, $nodeProcessors
	) {
		parent::__construct( $revision );
		$this->nodeProcessors = array_filter(
			$nodeProcessors,
			static function ( INodeProcessor $processor ) {
				return $processor instanceof IMenuNodeProcessor;
			}
		);
	}

	/**
	 * @return INode[]
	 */
	public function parse(): array {
		$this->nodes = [];
		$content = $this->getRevision()->getContent( SlotRecord::MAIN );
		$text = $content->getText();
		$this->setRawData( $text );

		$lines = explode( "\n", $text );
		foreach ( $lines as $line ) {
			$this->tryGetNode( $line );
		}

		return $this->nodes;
	}

	/**
	 * @inheritDoc
	 */
	public function addNodesFromData( array $nodes, bool $replace = false ) {
		if ( $replace ) {
			// Clear wikitext
			$this->setRawData( '' );
		}
		foreach ( $nodes as $nodeData ) {
			$node = $this->getNodeFromData( $nodeData );
			if ( $node ) {
				$this->addNode( $node );
			}
		}
	}

	/**
	 * @param INode $node
	 * @param mixed|null $afterNode
	 * @param bool $newline
	 * @return void
	 * @throws MWException
	 */
	public function addNodeAfter( INode $node, mixed $afterNode, bool $newline = true ): void {
		if ( $afterNode === null ) {
			$this->addNode( $node, 'prepend', $newline );
			return;
		}
		$this->parse();
		if ( !( $afterNode instanceof MenuNode ) ) {
			$afterNode = $this->getNodeFromData( $afterNode );
			if ( !$afterNode ) {
				return;
			}
		}
		$afterNodeText = preg_quote( $afterNode->getOriginalData(), '/' );
		$newNodeText = $node->getOriginalData();
		if ( $newline ) {
			$newNodeText = "\n" . $newNodeText;
		}
		$this->rawData = preg_replace(
			'/' . $afterNodeText . '/',
			$afterNode->getOriginalData() . $newNodeText,
			$this->rawData,
			1
		);
		$this->setRevisionContent();
	}

	/**
	 * @param array $nodeData
	 * @return MenuNode|null
	 */
	public function getNodeFromData( array $nodeData ): ?MenuNode {
		if ( !isset( $nodeData['type'] ) ) {
			return null;
		}
		foreach ( $this->nodeProcessors as $processor ) {
			if ( $processor->supportsNodeType( $nodeData['type'] ) ) {
				$node = $processor->getNodeFromData( $nodeData );
				if ( !( $node instanceof MenuNode ) ) {
					continue;
				}
				return $node;
			}
		}
		return null;
	}

	/**
	 * @param string $line
	 */
	private function tryGetNode( $line ) {
		// Menu items are a bit specific, their syntax overlaps
		// eg, every single node will be matches by raw-text, as it matches everything
		// Therefore we allow last handling node to be choosen, while registration
		// is done in order of shrinking scopes (processor that matches most is first)
		$handlingProcessor = null;
		foreach ( $this->nodeProcessors as $key => $processor ) {
			if ( $processor->matches( $line ) ) {
				$handlingProcessor = $processor;
			}
		}
		if ( $handlingProcessor ) {
			$this->nodes[] = $handlingProcessor->getNode( new WikitextSource( $line ) );
		}
	}
}
