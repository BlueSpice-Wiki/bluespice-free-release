<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

interface ISkinSlotRegistry {

	/**
	 *
	 * @param array $slotSpecs
	 */
	public function __construct( $slotSpecs );

	/**
	 *
	 * @param string $skinSlot
	 * @param IComponent[] $components
	 * @return void
	 */
	public function register( $skinSlot, $components );

	/**
	 *
	 * @return array
	 */
	public function getSkinSlots(): array;
}
