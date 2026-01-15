<?php

namespace BlueSpice\Discovery\EnhancedSidebar\NodeProcessor;

use BlueSpice\Discovery\EnhancedSidebar\Node\PanelHeadingNode;
use MWStake\MediaWiki\Lib\Nodes\INode;

class PanelHeadingProcessor extends EnhancedSidebarNodeProcessor {

	/**
	 * @param string $type
	 * @return bool
	 */
	public function supportsNodeType( $type ): bool {
		return $type === 'enhanced-sidebar-panel-heading';
	}

	/**
	 * @param array $data
	 * @return INode
	 */
	public function getNodeFromData( array $data ): INode {
		return new PanelHeadingNode( $data );
	}
}
