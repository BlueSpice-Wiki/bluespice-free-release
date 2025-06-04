<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\ContentDroplets\Droplet;

use MediaWiki\Extension\ContentDroplets\IDropletDescription;

abstract class MagicWordDroplet implements IDropletDescription {

	/**
	 * Get target for the template
	 * @return string
	 */
	abstract protected function getMagicWord(): string;

}
