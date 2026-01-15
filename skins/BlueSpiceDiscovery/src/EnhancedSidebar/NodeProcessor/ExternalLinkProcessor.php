<?php

namespace BlueSpice\Discovery\EnhancedSidebar\NodeProcessor;

use BlueSpice\Discovery\EnhancedSidebar\Node\EnhancedSidebarNode;
use BlueSpice\Discovery\EnhancedSidebar\Node\ExternalLinkNode;
use Exception;
use MWStake\MediaWiki\Lib\Nodes\INode;

class ExternalLinkProcessor extends EnhancedSidebarNodeProcessor {

	/**
	 * @param string $type
	 * @return bool
	 */
	public function supportsNodeType( $type ): bool {
		return $type === 'enhanced-sidebar-external-link';
	}

	/**
	 * @param array $data
	 * @return INode
	 */
	public function getNodeFromData( array $data ): INode {
		$node = new ExternalLinkNode( $data );
		return $node;
	}

	/**
	 * Serialize in format to be consumed by a tree
	 *
	 * @param EnhancedSidebarNode $node
	 *
	 * @return array
	 *
	 * @throws Exception
	 */
	public function serializeNodeTree( EnhancedSidebarNode $node ): array {
		return parent::serializeNodeTree( $node ) + [
				'href' => $node->getHref(),
			];
	}
}
