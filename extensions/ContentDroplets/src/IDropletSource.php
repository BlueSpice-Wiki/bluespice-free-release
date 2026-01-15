<?php

namespace MediaWiki\Extension\ContentDroplets;

interface IDropletSource {
	/**
	 * @return IDropletDescription[]
	 */
	public function getDroplets(): array;
}
