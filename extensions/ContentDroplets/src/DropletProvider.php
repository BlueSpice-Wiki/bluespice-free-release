<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\ContentDroplets;

class DropletProvider {
	/** @var IDropletSource[] */
	private $sources;
	/** @var IDropletDescription[] */
	private $droplets;

	/**
	 * @param IDropletSource $sources
	 */
	public function __construct( array $sources ) {
		$this->sources = $sources;
	}

	/**
	 * @return array
	 */
	public function getDroplets(): array {
		$this->assertSourcesLoaded();

		return $this->droplets;
	}

	private function assertSourcesLoaded() {
		if ( $this->droplets === null ) {
			$this->droplets = [];
			foreach ( $this->sources as $key => $source ) {
				$this->droplets = array_merge( $this->droplets, $source->getDroplets() );
			}
		}
	}
}
