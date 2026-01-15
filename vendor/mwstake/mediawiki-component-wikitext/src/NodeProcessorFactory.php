<?php

namespace MWStake\MediaWiki\Component\Wikitext;

use MWStake\MediaWiki\Component\ManifestRegistry\ManifestAttributeBasedRegistry;
use MWStake\MediaWiki\Lib\Nodes\INodeProcessor;
use Wikimedia\ObjectFactory\ObjectFactory;

class NodeProcessorFactory {
	/** @var array */
	private $processors;

	/**
	 * @param array $globalRegistry
	 * @param ManifestAttributeBasedRegistry $attributeRegistry
	 * @param ObjectFactory $objectFactory
	 */
	public function __construct(
		array $globalRegistry, ManifestAttributeBasedRegistry $attributeRegistry,
		ObjectFactory $objectFactory
	) {
		$this->instantiate(
			array_merge( $globalRegistry, $attributeRegistry->getAllValues() ),
			$objectFactory
		);
	}

	/**
	 * @param array $registry
	 * @param ObjectFactory $objectFactory
	 */
	private function instantiate( array $registry, ObjectFactory $objectFactory ) {
		foreach ( $registry as $key => $spec ) {
			$processor = $objectFactory->createObject( $spec );
			if ( !( $processor instanceof INodeProcessor ) ) {
				continue;
			}
			$this->processors[$key] = $processor;
		}
	}

	/**
	 * @return array
	 */
	public function getAll(): array {
		return $this->processors;
	}

	/**
	 * @param string $key
	 * @return INodeProcessor|null
	 */
	public function getByKey( $key ): ?INodeProcessor {
		return $this->processors[$key] ?? null;
	}
}
