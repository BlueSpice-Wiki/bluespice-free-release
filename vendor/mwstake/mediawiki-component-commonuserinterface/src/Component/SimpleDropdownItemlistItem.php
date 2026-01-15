<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface\Component;

use MWStake\MediaWiki\Component\CommonUserInterface\IDropdownItemlistItem;

class SimpleDropdownItemlistItem extends ComponentBase implements IDropdownItemlistItem {

	/**
	 * @param array $options
	 */
	public function __construct( $options ) {
		$this->options = array_merge(
			[
				'id' => 'simple-dropdown-list-item',
				'classes' => [],
				'items' => []
			],
			$options
		);
	}

	/**
	 * @inheritDoc
	 */
	public function getId(): string {
		return $this->options['id'];
	}

	/**
	 *
	 * @return string[]
	 */
	public function getClasses(): array {
		return $this->options['classes'];
	}
}
