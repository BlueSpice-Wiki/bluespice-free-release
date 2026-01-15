<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\ContentDroplets;

class DropletSerializer {
	/**
	 * @param array $droplets
	 * @return array
	 */
	public function serialize( array $droplets ): array {
		$serialized = [];

		/**
		 * @var string $key
		 * @var IDropletDescription $droplet
		 */
		foreach ( $droplets as $key => $droplet ) {
			$modules = [];

			if ( $droplet->getRLModules() ) {
				foreach ( $droplet->getRLModules() as $module ) {
					$modules[] = $module;
				}
			}

			$serialized[$key] = [
				'name' => $droplet->getName()->plain(),
				'description' => $droplet->getDescription()->plain(),
				'icon' => $droplet->getIcon(),
				'rlModules' => $modules,
				'categories' => $droplet->getCategories(),
				'content' => $droplet->getContent(),
				'veCommand' => $droplet->getVeCommand()
			];
		}

		return $serialized;
	}
}
