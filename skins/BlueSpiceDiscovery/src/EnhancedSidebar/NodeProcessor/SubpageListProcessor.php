<?php

namespace BlueSpice\Discovery\EnhancedSidebar\NodeProcessor;

use BlueSpice\Discovery\EnhancedSidebar\Node\EnhancedSidebarNode;
use BlueSpice\Discovery\EnhancedSidebar\Node\SubpageListNode;
use Exception;
use MWStake\MediaWiki\Lib\Nodes\INode;

class SubpageListProcessor extends InternalLinkProcessor {

	/**
	 * @param string $type
	 * @return bool
	 */
	public function supportsNodeType( $type ): bool {
		return $type === 'enhanced-sidebar-subpage-tree';
	}

	/**
	 * @param array $data
	 * @return INode
	 */
	public function getNodeFromData( array $data ): INode {
		$data['depth'] = is_numeric( $data['depth'] ) ? (int)$data['depth'] : 1;
		return new SubpageListNode( $data );
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
		$data = parent::serializeNodeTree( $node );
		$classes = array_diff( $node->getOutputCssClasses(), [ 'new' ] );

		$title = $this->getTitleFromNode( $node );
		if ( !$title ) {
			return $data;
		}

		if ( $this->isActivePageNode( $title ) ) {
			$classes = array_merge( $classes, [ 'active' ] );
		}

		$hasSubpages = $title->hasSubpages();
		$titleDBKey = $title->getPrefixedDBkey();
		if ( $title->getNamespace() === 0 ) {
			$titleDBKey = ':' . $titleDBKey;
		}

		return array_merge(
			$data,
			[
				'data' => [ 'root' => $titleDBKey, 'depth' => $node->getDepth() ],
				'classes' => $classes,
				'items' => [],
				'isLeaf' => !$hasSubpages
			]
		);
	}

	/**
	 * @inheritDoc
	 */
	protected function getKeysToPreprocess( array $data ): array {
		return array_merge( parent::getKeysToPreprocess( $data ), [ 'depth', 'page' ] );
	}
}
