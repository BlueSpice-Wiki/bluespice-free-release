<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\MenuEditor;

use Wikimedia\ObjectFactory\ObjectFactory;

class MenuFactory {
	/** @var MenuAttributeRegistry */
	private $registry;
	/** @var ObjectFactory */
	private $objectFactory;
	/** @var null */
	private $menus = null;

	/**
	 * @param MenuAttributeRegistry $registry
	 * @param ObjectFactory $objectFactory
	 */
	public function __construct( MenuAttributeRegistry $registry, ObjectFactory $objectFactory ) {
		$this->registry = $registry;
		$this->objectFactory = $objectFactory;
	}

	public function initialize() {
		$this->assertLoaded();
	}

	/**
	 * @return IMenu[]
	 */
	public function getAllMenus(): array {
		$this->assertLoaded();
		return $this->menus;
	}

	private function assertLoaded() {
		if ( $this->menus === null ) {
			$this->menus = [];
			$data = $this->registry->getAllValues();

			foreach ( $data as $key => $spec ) {
				$object = $this->objectFactory->createObject( $spec );
				if ( !( $object instanceof IMenu ) ) {
					continue;
				}
				$this->menus[$key] = $object;
			}
		}
	}
}
