<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

class SkinSlotRegistry implements ISkinSlotRegistry {

	/**
	 *
	 * @var array
	 */
	private $slotSpecs = [];

	/**
	 *
	 * @var SkinSlotRegistry|null
	 */
	private static $instance = null;

	/**
	 *
	 * @param array $slotSpecs
	 * @return SkinSlotRegistry
	 */
	public static function singleton( $slotSpecs ): SkinSlotRegistry {
		if ( static::$instance == null ) {
			static::$instance = new SkinSlotRegistry(
				$slotSpecs
			);
		}
		return static::$instance;
	}

	/**
	 *
	 * @param array $slotSpecs
	 */
	public function __construct( $slotSpecs ) {
		$this->slotSpecs = $slotSpecs;
	}

	/**
	 *
	 * @param string $skinSlot
	 * @param IComponent[] $components
	 * @return void
	 */
	public function register( $skinSlot, $components ) {
		if ( !array_key_exists( $skinSlot, $this->slotSpecs ) ) {
			$this->slotSpecs[$skinSlot] = [];
		}
		$this->slotSpecs[$skinSlot] = array_merge( $this->slotSpecs[$skinSlot], $components );
	}

	/**
	 *
	 * @return array
	 */
	public function getSkinSlots(): array {
		return $this->slotSpecs;
	}

	/**
	 * @param string $regKey
	 * @return array
	 */
	public function getSkinSlot( $regKey ): array {
		if ( array_key_exists( $regKey, $this->slotSpecs ) ) {
			return $this->slotSpecs[$regKey];
		}
		return [];
	}
}
