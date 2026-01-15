<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

use Exception;
use Wikimedia\ObjectFactory\ObjectFactory;

class ComponentRendererFactory {

	/**
	 *
	 * @var array
	 */
	private $rendererRegistry = [];

	/**
	 *
	 * @var array
	 */
	private $componentRegistry = [];

	/**
	 *
	 * @var string
	 */
	private $rendererType = '';

	/**
	 *
	 * @var ObjectFactory
	 */
	private $objectFactory = null;

	/**
	 *
	 * @param array $rendererRegistry
	 * @param array $componentRegistry
	 * @param array $rendererType
	 * @param array $objectFactory
	 */
	public function __construct( $rendererRegistry, $componentRegistry, $rendererType,
	$objectFactory ) {
		$this->rendererRegistry = $rendererRegistry;
		$this->componentRegistry = $componentRegistry;
		$this->rendererType = $rendererType;
		$this->objectFactory = $objectFactory;
	}

	/**
	 * Get key for use with `RendererFactory::getRenderer`
	 *
	 * @param IComponent $component
	 * @return string
	 */
	public function getKey( $component ) {
		foreach ( $this->componentRegistry as $key => $interface ) {
			if ( $component instanceof $interface ) {
				return $key;
			}
		}

		$className = get_class( $component );
		throw new Exception( "No interface for '$className' registered!" );
	}

	/**
	 *
	 * @param string $rendererKey
	 * @return IComponentRenderer
	 */
	public function getRenderer( $rendererKey ): IComponentRenderer {
		$spec = [];

		// Renderer available for current environment?
		if ( isset( $this->rendererRegistry[$this->rendererType][$rendererKey] ) ) {
			$spec = $this->rendererRegistry[$this->rendererType][$rendererKey];
		} elseif ( isset( $this->rendererRegistry['*'][$rendererKey] ) ) {
			// Try to fall back to "generic" renderer
			$spec = $this->rendererRegistry['*'][$rendererKey];
		}
		// Convert simple registration to `ObjectFactory` compatible spec
		if ( is_string( $spec ) && !empty( $spec ) ) {
			$callback = $spec;
			$spec = [
				'class' => $callback
			];
		}
		if ( empty( $spec ) ) {
			throw new Exception( "No renderer for '$rendererKey' registered!" );
		}

		$renderer = $this->objectFactory->createObject( $spec );
		return $renderer;
	}

	/**
	 *
	 * @return IComponentRenderer[]
	 */
	public function getAllRenderers(): array {
		$componentKeys = array_keys( $this->componentRegistry );
		$renderers = [];
		foreach ( $componentKeys as $key ) {
			$renderers[$key] = $this->getRenderer( $key );
		}

		return $renderers;
	}

}
