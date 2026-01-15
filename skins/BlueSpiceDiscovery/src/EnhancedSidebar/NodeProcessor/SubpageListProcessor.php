<?php

namespace BlueSpice\Discovery\EnhancedSidebar\NodeProcessor;

use BlueSpice\Discovery\EnhancedSidebar\Node\SubpageListNode;
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
		return new SubpageListNode( $this->getPermissionManager(), $data );
	}

	/**
	 * @inheritDoc
	 */
	protected function getKeysToPreprocess( array $data ): array {
		return array_merge( parent::getKeysToPreprocess( $data ), [ 'depth', 'page' ] );
	}
}
