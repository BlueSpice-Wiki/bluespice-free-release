<?php

namespace MWStake\MediaWiki\Component\ManifestRegistry;

use Psr\Log\LoggerInterface;
use Wikimedia\ObjectFactory\ObjectFactory;

class ManifestObjectFactory {

	/**
	 * @var ManifestRegistryFactory
	 */
	private $registryFactory = null;

	/**
	 * @var ObjectFactory
	 */
	private $objectFactory = null;

	/**
	 * @var LoggerInterface
	 */
	private $logger = null;

	/**
	 * @param ManifestRegistryFactory $registryFactory
	 * @param ObjectFactory $objectFactory
	 * @param LoggerInterface $logger
	 * @return void
	 */
	public function __construct(
		ManifestRegistryFactory $registryFactory,
		ObjectFactory $objectFactory,
		LoggerInterface $logger
	) {
		$this->registryFactory = $registryFactory;
		$this->objectFactory = $objectFactory;
		$this->logger = $logger;
	}

	/**
	 * @param string $registryName
	 * @param string $registryKey
	 * @param array $options
	 * @param string|null $instanceof
	 * @phpcs:ignore MediaWiki.Commenting.FunctionComment.ObjectTypeHintReturn
	 * @return object|null
	 */
	public function createObject(
		string $registryName,
		string $registryKey,
		array $options = [],
		?string $instanceof = null
	): ?object {
		/** @var ManifestAttributeBasedRegistry */
		$registry = $this->registryFactory->get( $registryName );

		$spec = $registry->getObjectSpec( $registryKey );

		if ( !isset( $spec['factory'] ) && !isset( $spec['class'] ) ) {
			return null;
		}

		$object = $this->objectFactory->createObject( $spec, $options );

		if ( ( $instanceof === null ) || is_a( $object, $instanceof, true ) ) {
			return $object;
		}

		return null;
	}

	/**
	 * @param string $registryName
	 * @param array $options
	 * @param string|null $instanceof
	 * @return array
	 */
	public function createAllObjects(
		string $registryName,
		array $options = [],
		?string $instanceof = null
	): array {
		/** @var ManifestAttributeBasedRegistry */
		$registry = $this->registryFactory->get( $registryName );
		$registryKeys = $registry->getAllKeys();

		$objects = [];
		foreach ( $registryKeys as $registryKey ) {
			$spec = $registry->getObjectSpec( $registryKey );
			$object = $this->createObject( $registryName, $registryKey, $options, $instanceof );
			if ( $object === null ) {
				$this->logger->warning(
					"The object of {key} in {registry} is not a instance of {instanceof}",
					[
						'key' => $registryKey,
						'registry' => $registryName,
						'instanceof' => $instanceof
					]
				);

				continue;
			}

			$objects[$registryKey] = $object;
		}

		return $objects;
	}

}
