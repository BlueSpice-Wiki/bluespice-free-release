<?php

namespace MWStake\MediaWiki\Component\FormEngine;

class FormLoaderSpecification {

	/**
	 * @param string $formClass
	 * @param array $resourceLoaderModules
	 */
	public function __construct(
		private readonly string $formClass,
		private readonly array $resourceLoaderModules
	) {
	}

	/**
	 * @return string
	 */
	public function getFormClass(): string {
		return $this->formClass;
	}

	/**
	 * @return array
	 */
	public function getResourceLoaderModules(): array {
		return $this->resourceLoaderModules;
	}
}
