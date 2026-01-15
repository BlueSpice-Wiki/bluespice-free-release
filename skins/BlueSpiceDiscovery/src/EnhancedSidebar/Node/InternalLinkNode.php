<?php

namespace BlueSpice\Discovery\EnhancedSidebar\Node;

use InvalidArgumentException;

class InternalLinkNode extends EnhancedSidebarNode {

	/** @var string|null */
	protected $target;

	/**
	 * @param array $data
	 */
	public function __construct( array $data ) {
		parent::__construct( $data );

		if ( !isset( $data['page'] ) ) {
			throw new InvalidArgumentException(
				$this->getType() . ' requires "target" parameter'
			);
		}
		$this->target = $data['page'];
	}

	/**
	 * @return string|null
	 */
	public function getTarget(): ?string {
		return $this->target;
	}

	/**
	 * @return string
	 */
	public function getType(): string {
		return 'enhanced-sidebar-internal-link';
	}

	/**
	 * @return array
	 */
	public function jsonSerialize(): array {
		return parent::jsonSerialize() + [
				'page' => $this->target
		];
	}

	/**
	 * @return string[]
	 */
	public function getOutputCssClasses(): array {
		$classes = parent::getOutputCssClasses();
		return array_merge( $classes, [ 'internal' ] );
	}
}
