<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\ContentDroplets;

use Exception;

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
	 * @throws Exception
	 */
	public function getDroplets(): array {
		$this->assertSourcesLoaded();
		$this->assertDropletVisibility();

		return $this->droplets;
	}

	/**
	 * @return void
	 */
	private function assertSourcesLoaded(): void {
		if ( $this->droplets === null ) {
			$this->droplets = [];
			foreach ( $this->sources as $key => $source ) {
				$this->droplets = array_merge( $this->droplets, $source->getDroplets() );
			}
		}
	}

	/**
	 * @return void
	 * @throws Exception
	 */
	private function assertDropletVisibility(): void {
		if ( $this->droplets === null ) {
			throw new Exception( "Droplet sources must be loaded first" );
		}

		$this->droplets = array_filter(
			$this->droplets,
			fn ( IDropletDescription $droplet ) => $droplet->listDroplet()
		);
	}
}
