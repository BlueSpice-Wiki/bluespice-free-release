<?php

namespace BlueSpice\ExtendedStatistics;

use RuntimeException;
use Wikimedia\ObjectFactory\ObjectFactory;

class AttributeRegistryFactory {
	/** @var array */
	private $registry;
	/** @var ObjectFactory */
	private $objectFactory;
	/** @var array */
	private $items = [];
	/** @var string */
	private $targetClass;

	/**
	 * @param array $registry
	 * @param ObjectFactory $objectFactory
	 * @param string $targetClass
	 */
	public function __construct( $registry, ObjectFactory $objectFactory, $targetClass ) {
		$this->registry = $registry;
		$this->objectFactory = $objectFactory;
		$this->targetClass = $targetClass;
	}

	/**
	 * @param string $type
	 * @return bool
	 */
	public function hasType( $type ) {
		return isset( $this->registry[$type] );
	}

	/**
	 * @param string $type
	 * @return stdClass
	 * @throws RuntimeException
	 */
	public function get( $type ) {
		if ( !isset( $this->providers[$type] ) ) {
			if ( !$this->hasType( $type ) ) {
				throw new RuntimeException( "Type $type is not registered" );
			}
			$specs = $this->registry[$type];
			if ( is_string( $specs ) && is_callable( $specs ) ) {
				$specs = [ 'factory' => $specs ];
			}

			$instance = $this->objectFactory->createObject( $specs );
			if ( !$instance instanceof $this->targetClass ) {
				$actual = get_class( $instance );
				$target = $this->targetClass;
				throw new RuntimeException(
					"Instance must implement $target, $actual given"
				);
			}

			$this->items[$type] = $instance;
		}

		return $this->items[$type];
	}

	/**
	 * @return array
	 */
	public function getAll(): array {
		foreach ( array_keys( $this->registry ) as $key ) {
			$this->get( $key );
		}

		return $this->items;
	}
}
