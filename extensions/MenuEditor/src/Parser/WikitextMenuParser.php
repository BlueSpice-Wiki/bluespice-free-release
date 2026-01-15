<?php

namespace MediaWiki\Extension\MenuEditor\Parser;

use MediaWiki\Extension\MenuEditor\IMenuNodeProcessor;
use MediaWiki\Extension\MenuEditor\Node\MenuNode;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRecord;
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
			if ( !isset( $nodeData['type'] ) ) {
				continue;
			}
			foreach ( $this->nodeProcessors as $processor ) {
				if ( $processor->supportsNodeType( $nodeData['type'] ) ) {
					$node = $processor->getNodeFromData( $nodeData );
					if ( !( $node instanceof MenuNode ) ) {
						continue;
					}
					$this->addNode( $node );
				}
			}
		}
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
