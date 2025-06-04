<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\ContentDroplets\DropletSource;

use InvalidArgumentException;
use MediaWiki\Extension\ContentDroplets\IDropletDescription;
use MediaWiki\Extension\ContentDroplets\IDropletSource;
use MediaWiki\Registration\ExtensionRegistry;
use Wikimedia\ObjectFactory\ObjectFactory;

class RegistrySource implements IDropletSource {
	/** @var array */
	protected $registry;
	/**
	 * @var ObjectFactory
	 */
	private ObjectFactory $objectFactory;

	/**
	 * @param array $registry
	 * @param ObjectFactory $objectFactory
	 */
	public function __construct( $registry, ObjectFactory $objectFactory ) {
		$this->registry = $registry;
		$this->objectFactory = $objectFactory;
	}

	/**
	 * @inheritDoc
	 */
	public function getDroplets(): array {
		$droplets = [];
		foreach ( $this->registry as $key => $spec ) {
			if ( isset( $spec['requires'] ) ) {
				$allowDroplet = $this->checkRequirements( $spec['requires'] );
				if ( !$allowDroplet ) {
					continue;
				}
			}
			$object = $this->objectFactory->createObject( $spec );
			if ( !( $object instanceof IDropletDescription ) ) {
				throw new InvalidArgumentException(
					"Invalid object spec for droplet source \"$key\""
				);
			}
			$droplets[$key] = $object;
		}
		return $droplets;
	}

	/**
	 *
	 * @param array $requirements
	 * @return bool
	 */
	private function checkRequirements( $requirements ) {
		$allow = true;
		foreach ( $requirements as $requirement => $value ) {
			if ( !ExtensionRegistry::getInstance()->isLoaded( $requirement ) ) {
				$allow = false;
			}
		}
		return $allow;
	}
}
