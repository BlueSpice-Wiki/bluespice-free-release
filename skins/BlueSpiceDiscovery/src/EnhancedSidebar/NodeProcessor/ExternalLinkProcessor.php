<?php

namespace BlueSpice\Discovery\EnhancedSidebar\NodeProcessor;

use BlueSpice\Discovery\EnhancedSidebar\Node\ExternalLinkNode;
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
		return new ExternalLinkNode( $data );
	}
}
