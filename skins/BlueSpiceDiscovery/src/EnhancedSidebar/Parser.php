<?php

namespace BlueSpice\Discovery\EnhancedSidebar;

use BlueSpice\Discovery\EnhancedSidebar\Node\EnhancedSidebarNode;
use BlueSpice\Discovery\EnhancedSidebar\NodeProcessor\EnhancedSidebarNodeProcessor;
use Exception;
use MediaWiki\Content\Content;
use MediaWiki\Content\JsonContent;
use MediaWiki\Extension\MenuEditor\Parser\IMenuParser;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\User\User;
use MWStake\MediaWiki\Lib\Nodes\IMutableNode;
use MWStake\MediaWiki\Lib\Nodes\INode;
use MWStake\MediaWiki\Lib\Nodes\INodeProcessor;
use MWStake\MediaWiki\Lib\Nodes\IParser;
use MWStake\MediaWiki\Lib\Nodes\MutableParser;

/**
 * This parser's implementation could be confusing. It is a parser for the
 * both the MenuEditor and the EnhancedSidebar output. So, depending on the context
 * it will do different things
 *
 */
class Parser extends MutableParser implements IParser, IMenuParser {

	/** @var EnhancedSidebarNodeProcessor[] */
	private $nodeProcessors;
	/** @var array */
	private $nodes = null;
	/** @var bool */
	private $doFullParse = false;

	/**
	 * @param RevisionRecord $revision
	 * @param array $nodeProcessors
	 */
	public function __construct( RevisionRecord $revision, array $nodeProcessors ) {
		parent::__construct( $revision );
		$this->nodeProcessors = array_filter(
			$nodeProcessors,
			static function ( INodeProcessor $processor ) {
				return $processor instanceof EnhancedSidebarNodeProcessor;
			}
		);
		$this->rawData = [];
	}

	/**
	 * @return array
	 */
	public function parse(): array {
		$content = $this->pullContent();
		if ( is_array( $this->nodes ) ) {
			return $this->nodes;
		}
		$this->nodes = [];
		$this->processNodesInternally( $content, 1 );
		return $this->nodes;
	}

	/**
	 * @param User $user
	 * @return array
	 */
	public function parseForOutput( User $user ): array {
		$this->setUserOnProcessors( $user );
		$this->setFullParse( true );
		$this->parse();
		$this->setFullParse( false );

		$data = [];
		/** @var EnhancedSidebarNode $node */
		foreach ( $this->nodes as $node ) {
			// Convert usual flat list of nodes into a tree
			if ( $node->isHidden( $user ) ) {
				continue;
			}
			if ( $node->getLevel() !== 1 ) {
				continue;
			}
			$nodeData = $node->treeSerialize() + $this->getTreeChildren( $node, $user );
			$nodeData['leaf'] = empty( $nodeData['items'] );
			$data[] = $nodeData;
		}

		return $data;
	}

	/**
	 * @param EnhancedSidebarNode $node
	 * @param User $user
	 *
	 * @return array[]
	 */
	private function getTreeChildren( EnhancedSidebarNode $node, User $user ) {
		$parentFound = false;
		$children = [];
		/** @var EnhancedSidebarNode $node */
		foreach ( $this->nodes as $childNode ) {
			if ( $childNode === $node ) {
				$parentFound = true;
				continue;
			}
			if ( $parentFound ) {
				if ( $childNode->getLevel() === $node->getLevel() + 1 ) {
					if ( !$childNode->isHidden( $user ) ) {
						$children[] = $childNode->treeSerialize() + $this->getTreeChildren( $childNode, $user );
					}
				} elseif ( $childNode->getLevel() <= $node->getLevel() ) {
					return [ 'items' => $children ];
				}
			}
		}

		return [ 'items' => $children ];
	}

	/**
	 * If set to true, will parse data values - for output
	 *
	 * @param bool $doFullParse
	 *
	 * @return void
	 */
	private function setFullParse( bool $doFullParse ) {
		$this->doFullParse = $doFullParse;
	}

	/**
	 * @return array
	 */
	public function pullContent(): array {
		$content = $this->revision->getContent( SlotRecord::MAIN );
		if ( !$content ) {
			throw new \UnexpectedValueException( 'Content of the page not readable' );
		}
		if ( !( $content instanceof JsonContent ) ) {
			throw new \UnexpectedValueException( 'Not a JSON content' );
		}
		$text = $content->getText();
		$data = json_decode( $text, true );
		if ( !$data ) {
			throw new Exception( json_last_error_msg() );
		}
		return $data;
	}

	/**
	 * @param array $nodes
	 * @param int $level
	 * @return void
	 */
	private function processNodesInternally( array $nodes, int $level ) {
		foreach ( $nodes as $nodeData ) {
			// Get node processor that supports the node type
			try {
				$nodeProcessor = $this->getNodeProcessor( $nodeData['type'] ?? '' );
			} catch ( Exception $e ) {
				continue;
			}

			if ( $this->doFullParse ) {
				$node = $nodeProcessor->getNode( new JsonNodeSource( $nodeData ) );
			} else {
				$node = $nodeProcessor->getRawNode( new JsonNodeSource( $nodeData ) );
			}
			if ( !( $node instanceof EnhancedSidebarNode ) ) {
				continue;
			}
			$node->setLevel( $level );
			$this->nodes[] = $node;
			if ( isset( $nodeData['children'] ) && is_array( $nodeData['children'] ) && $node->supportsChildren() ) {
				$this->processNodesInternally( $nodeData['children'], $level + 1 );
			}
		}
	}

	/**
	 * @param string $type
	 * @return EnhancedSidebarNodeProcessor
	 */
	private function getNodeProcessor( string $type ): EnhancedSidebarNodeProcessor {
		foreach ( $this->nodeProcessors as $nodeProcessor ) {
			if ( $nodeProcessor->supportsNodeType( $type ) ) {
				return $nodeProcessor;
			}
		}
		throw new \UnexpectedValueException( 'No node processor found for type ' . $type );
	}

	/**
	 * @return Content
	 */
	protected function getContentObject(): Content {
		return new JsonContent( $this->getMutatedData() );
	}

	/**
	 * @return string|null
	 */
	public function getMutatedData(): ?string {
		if ( !$this->isMutated() ) {
			return null;
		}
		return json_encode( $this->rawData, JSON_PRETTY_PRINT );
	}

	/**
	 * @param INode $node
	 * @param string $mode
	 * @param bool $newline
	 * @return void
	 */
	public function addNode( INode $node, $mode = 'append', $newline = true ): void {
		$this->rawData[] = $node->storageSerialize();
		$this->setRevisionContent();
	}

	/**
	 * @param IMutableNode $node
	 * @return bool
	 */
	public function replaceNode( IMutableNode $node ): bool {
		return true;
	}

	/**
	 * @param INode $node
	 * @return bool
	 */
	public function removeNode( INode $node ): bool {
		return true;
	}

	/**
	 * @param array $nodes
	 * @param bool $replace
	 *
	 * @return void
	 */
	public function addNodesFromData( array $nodes, bool $replace = false ) {
		$parsed = [];
		foreach ( $nodes as $nodeData ) {
			// Get node processor that supports the node type
			try {
				$nodeProcessor = $this->getNodeProcessor( $nodeData['type'] ?? '' );
			} catch ( Exception $e ) {
				continue;
			}
			$node = $nodeProcessor->getRawNode( new JsonNodeSource( $nodeData ) );
			if ( !( $node instanceof EnhancedSidebarNode ) ) {
				continue;
			}
			$parsed[] = $node;
		}
		foreach ( $this->assignChildren( $parsed ) as $node ) {
			$this->addNode( $node );
		}
	}

	/**
	 * Add nodes to one another based on their level
	 * @param array $parsed
	 *
	 * @return array
	 */
	private function assignChildren( array $parsed ): array {
		$nodes = [];
		/** @var EnhancedSidebarNode $node */
		foreach ( $parsed as $node ) {
			if ( $node->getLevel() !== 1 ) {
				continue;
			}
			$this->addChildren( $node, $parsed );
			$nodes[] = $node;
		}
		return $nodes;
	}

	/**
	 * @param EnhancedSidebarNode $node
	 * @param array $nodes
	 *
	 * @return void
	 */
	private function addChildren( EnhancedSidebarNode $node, array $nodes ) {
		$found = false;
		foreach ( $nodes as $n ) {
			if ( $n === $node ) {
				$found = true;
				continue;
			}
			if ( $found ) {
				if ( $n->getLevel() === $node->getLevel() ) {
					return;
				}
				if ( $n->getLevel() === $node->getLevel() + 1 ) {
					$this->addChildren( $n, $nodes );
					$node->addChild( $n );
				}
			}
		}
	}

	/**
	 * @param User $user
	 *
	 * @return void
	 */
	private function setUserOnProcessors( User $user ) {
		foreach ( $this->nodeProcessors as $processor ) {
			$processor->setUser( $user );
		}
	}
}
