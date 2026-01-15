<?php

namespace BlueSpice\Discovery;

interface IResourceProvider {

	/**
	 * @return array
	 */
	public function getStyles(): array;

	/**
	 * @return array
	 */
	public function getScripts(): array;
}
