<?php

namespace MWStake\MediaWiki\Component\FormEngine;

interface IFormSpecification {
	/**
	 * @return string[]
	 */
	public function getSerialized(): array;
}
