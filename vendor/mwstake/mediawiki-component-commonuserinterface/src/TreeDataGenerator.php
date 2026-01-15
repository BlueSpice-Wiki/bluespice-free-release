<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

use Wikimedia\ObjectFactory\ObjectFactory;

class TreeDataGenerator {

	/**
	 * @var ObjectFactory
	 */
	private $objectFactory = null;

	/**
	 * @var string
	 */
	private $validInterface = 'MWStake\MediaWiki\Component\CommonUserInterface\ITreeNode';

	/**
	 * @var array
	 */
	private $treeNodeRegistry = [];

	/**
	 * @param array $treeNodeRegistry
	 * @param ObjectFactory $objectFactory
	 */
	public function __construct( array $treeNodeRegistry, ObjectFactory $objectFactory ) {
		$this->treeNodeRegistry = $treeNodeRegistry;
		$this->objectFactory = $objectFactory;
	}

	/**
	 * @param array $data
	 * @param array $expandPaths
	 * @return ITreeNode[]
	 */
	public function generate( array $data, array $expandPaths ): array {
		$nodes = [];

		foreach ( $data as $nodeOptions ) {
			$nodeOptions = array_merge(
				[
					'id' => '',
					'items' => [],
					'expanded' => false
				],
				$nodeOptions
			);

			$childExpandPaths = [];
			if ( $this->hasChildren( $nodeOptions ) ) {
				$isExpanded = $this->isExpanded( $nodeOptions['id'], $expandPaths );
				if ( $isExpanded ) {
					$nodeOptions['expanded'] = true;
				}

				$childExpandPaths = $this->getChildExpandPaths( $nodeOptions['id'], $expandPaths );
			}

			$node = $this->createNode( $nodeOptions, $childExpandPaths );

			if ( is_a( $node, $this->validInterface, true ) ) {
				$nodes[] = $node;
			}
		}
		return $nodes;
	}

	/**
	 * @param string $id
	 * @param array $expandPaths
	 * @return bool
	 */
	private function isExpanded( string $id, array $expandPaths ): bool {
		foreach ( $expandPaths as $expandPath ) {
			$path = explode( '/', $expandPath );

			if ( $path[0] === $id ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param string $id
	 * @param array $expandPaths
	 * @return array
	 */
	private function getChildExpandPaths( string $id, array $expandPaths ): array {
		$childExpandPaths = [];

		for ( $index = 0; $index < count( $expandPaths ); $index++ ) {
			$path = explode( '/', $expandPaths[$index] );

			if ( $path[0] === $id ) {
				$childPath = array_slice( $path, 1 );
				$childExpandPaths[] = implode( '/', $childPath );
			}
		}

		return $childExpandPaths;
	}

	/**
	 * @param array $options
	 * @param array $childExpandPaths
	 * @return ITreeNode|null
	 */
	private function createNode( array $options, $childExpandPaths ): ?ITreeNode {
		$nodeType = $this->getNodeType( $options );
		$nodeName = $this->getNodeName( $nodeType );

		if ( !isset( $this->treeNodeRegistry[$nodeName] ) ) {
			return false;
		}

		$spec = $this->treeNodeRegistry[$nodeName];
		$node = $this->objectFactory->createObject( $spec );

		if ( $this->hasChildren( $options ) ) {
			$options['items'] = $this->generate( $options['items'], $childExpandPaths );
		}
		$node->setNodeOptions( $options );

		return $node;
	}

	/**
	 * @param array $options
	 * @return bool
	 */
	private function hasChildren( array $options ): bool {
		if ( isset( $options['items'] ) && !empty( $options['items'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @param array $nodeSpec
	 * @return string
	 */
	private function getNodeType( array $nodeSpec ): string {
		$nodeType = 'text';

		if ( isset( $nodeSpec['href'] ) ) {
			$nodeType = 'link';
		}

		return $nodeType;
	}

	/**
	 * Get the name of the node type to create a object
	 * Examples:
	 * - For type 'text' create node of 'tree-text-node'
	 * - For type 'link' create node of 'tree-link-node'
	 *
	 * @param string $nodeType
	 * @return string
	 */
	private function getNodeName( string $nodeType ): string {
		return "tree-$nodeType-node";
	}
}
