<?php

namespace BlueSpice\Discovery\EnhancedSidebar\Node;

use InvalidArgumentException;

class ExternalLinkNode extends EnhancedSidebarNode {

	/** @var string */
	private $href;

	/**
	 * @param array $data
	 */
	public function __construct( array $data ) {
		parent::__construct( $data );
		if ( !isset( $data['href'] ) ) {
			throw new InvalidArgumentException(
				$this->getType() . ' requires "href" parameter'
			);
		}
		$this->href = $data['href'];
	}

	/**
	 * @return string
	 */
	public function getHref(): string {
		return $this->href;
	}

	/**
	 * @return string
	 */
	public function getType(): string {
		return 'enhanced-sidebar-external-link';
	}

	/**
	 * @return array
	 */
	public function jsonSerialize(): array {
		return parent::jsonSerialize() + [
			'href' => $this->href,
		];
	}

	/**
	 * @return string[]
	 */
	public function getOutputCssClasses(): array {
		return [ 'external' ] + parent::getOutputCssClasses();
	}
}
