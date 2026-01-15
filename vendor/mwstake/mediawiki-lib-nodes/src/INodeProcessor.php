<?php

namespace MWStake\MediaWiki\Lib\Nodes;

interface INodeProcessor {
	/**
	 * @param string $type
	 * @return string
	 */
	public function supportsNodeType( $type ): bool;

	/**
	 * Provide a node from serialized node data
	 *
	 * @param array $data
	 * @return INode
	 */
	public function getNodeFromData( array $data ): INode;

	/**
	 * @param INodeSource $nodeSource
	 * @return INode
	 */
	public function getNode( INodeSource $nodeSource ): INode;
}
