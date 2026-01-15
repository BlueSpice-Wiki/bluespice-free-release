<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

use Wikimedia\ObjectFactory\ObjectFactory;

class SkinSlotRendererFactory {

	/**
	 *
	 * @var ObjectFactory
	 */
	private $objectFactory = null;

	/**
	 *
	 * @var array
	 */
	private $registry = [];

	/**
	 *
	 * @param array $registry
	 * @param ObjectFactory $objectFactory
	 */
	public function __construct( $registry, $objectFactory ) {
		$this->registry = $registry;
		$this->objectFactory = $objectFactory;
	}

	/**
	 * @param string $slotId
	 * @return ISkinSlotRenderer
	 */
	public function create( string $slotId ): ISkinSlotRenderer {
		$spec = [];
		if ( isset( $this->registry[$slotId] ) ) {
			$spec = $this->registry[$slotId];
		}
		if ( empty( $spec ) ) {
			// Fall back to generic
			$spec = [
				'class' => GenericSkinSlotRenderer::class,
				'services' => [
					'MWStakeCommonUIComponentManager',
					'MWStakeCommonUIRendererDataTreeBuilder',
					'MWStakeCommonUIRendererDataTreeRenderer'
				],
				'args' => [ $slotId ]
			];
		}

		$renderer = $this->objectFactory->createObject( $spec );
		return $renderer;
	}

}
