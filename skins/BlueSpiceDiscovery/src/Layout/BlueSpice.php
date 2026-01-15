<?php

namespace BlueSpice\Discovery\Layout;

class BlueSpice extends SkinLayoutBase {

	/**
	 *
	 * @return string
	 */
	public function getName(): string {
		return 'bluespice';
	}

	/**
	 * @return array
	 */
	public function getStructureElementNames(): array {
		return [
			"skip-to",
			"navbar-primary",
			"main",
			"aftercontent",
			"footer",
			"back-to-top",
			"sidebar-primary",
			"sidebar-secondary",
		];
	}
}
