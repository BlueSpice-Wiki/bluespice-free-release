<?php

namespace MWStake\MediaWiki\Component\GenericTagHandler;

use MediaWiki\Config\Config;
use MediaWiki\HookContainer\HookContainer;
use MediaWiki\MediaWikiServices;
use RuntimeException;
use Wikimedia\ObjectFactory\ObjectFactory;

class TagFactory {

	/** @var array|null */
	private ?array $tags = null;

	/**
	 * @param HookContainer $hookContainer
	 * @param Config $mwsgConfig
	 * @param ObjectFactory $objectFactory
	 * @param MediaWikiServices $services
	 */
	public function __construct(
		private readonly HookContainer $hookContainer,
		private readonly Config $mwsgConfig,
		private readonly ObjectFactory $objectFactory,
		private readonly MediaWikiServices $services
	) {
	}

	/**
	 *
	 * @return ITag[]
	 */
	public function getAll() {
		$this->init();
		return $this->tags;
	}

	/**
	 * @return void
	 */
	private function init() {
		if ( $this->tags === null ) {
			$this->tags = [];
			$this->initFromRegistry();
			$this->initFromHook();
		}
	}

	/**
	 * @param ITag $tag
	 * @return void
	 */
	private function register( ITag $tag ) {
		$this->tags[] = $tag;
	}

	/**
	 * @return void
	 */
	private function initFromRegistry() {
		foreach ( $this->mwsgConfig->get( 'GenericTagRegistry' ) as $spec ) {
			$this->tags[] = $this->createFromSpec( $spec );
		}
		$this->assertUnique();
	}

	/**
	 * @return void
	 */
	private function initFromHook() {
		$this->hookContainer->run( 'MWStakeGenericTagHandlerInitTags', [ &$this->tags ] );
		if ( !is_array( $this->tags ) ) {
			throw new RuntimeException(
				'MWStakeGenericTagHandlerInitTags changed parameter value to a non-array'
			);
		}
		array_walk( $this->tags, static function ( $tag ) {
			if ( !$tag instanceof ITag ) {
				throw new RuntimeException(
					'MWStakeGenericTagHandlerInitTags hook added an object of type '
					. get_class( $tag ) . ' to the tags array.'
				);
			}
		} );
		$this->assertUnique();
	}

	/**
	 * @param array $spec
	 * @return ITag
	 */
	private function createFromSpec( array $spec ): ITag {
		$object = $this->objectFactory->createObject( $spec );
		return !$object instanceof ITag ? throw new \InvalidArgumentException(
			'Tag object must implement ITag interface. ' .
			'Class: ' . get_class( $object )
		) : $object;
	}

	/**
	 * @return void
	 */
	private function assertUnique() {
		$names = [];
		foreach ( $this->tags as $tag ) {
			foreach ( $tag->getTagNames() as $name ) {
				if ( in_array( $name, $names, true ) ) {
					throw new RuntimeException(
						'Tag name ' . $name . ' redefined in  ' . get_class( $tag )
					);
				}
				$names[] = $name;
			}
		}
	}

	/**
	 * @param ITag $tag
	 * @return TagRenderer
	 */
	public function makeTagRendererForTag( ITag $tag ): TagRenderer {
		return $this->objectFactory->createObject( [
			'class' => TagRenderer::class,
			'args' => [ $tag, $this->services ],
			'services' => [ 'ParserFactory', 'MWStake.InputProcessor' ]
		] );
	}

}
