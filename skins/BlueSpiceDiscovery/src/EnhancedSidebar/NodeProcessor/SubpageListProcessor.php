<?php

namespace BlueSpice\Discovery\EnhancedSidebar\NodeProcessor;

use BlueSpice\Discovery\EnhancedSidebar\Node\EnhancedSidebarNode;
use BlueSpice\Discovery\EnhancedSidebar\Node\SubpageListNode;
use BlueSpice\Discovery\SubpageDataGenerator;
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

		return array_merge(
			$data,
			[
				'classes' => $classes,
				'items' => $this->getNodeSubpages( $node ),
			]
		);
	}

	/**
	 *
	 * @param EnhancedSidebarNode $node
	 *
	 * @return array
	 */
	private function getNodeSubpages( EnhancedSidebarNode $node ): array {
		$title = $this->getTitleFromNode( $node );
		if ( !$title || !$title->hasSubpages() ) {
			return [];
		}

		$subpageDataGenerator = new SubpageDataGenerator();
		$subpageDataGenerator->setTreeRootTitle( $title );

		return $subpageDataGenerator->generate( $title, $node->getDepth() );
	}

	/**
	 * @inheritDoc
	 */
	protected function getKeysToPreprocess( array $data ): array {
		return array_merge( parent::getKeysToPreprocess( $data ), [ 'depth', 'page' ] );
	}
}
