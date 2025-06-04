<?php

namespace BlueSpice\InstanceStatus;

interface IApiStatusProvider {
	/**
	 * Label for the item layout
	 *
	 * @return string
	 */
	public function getKeyForApi(): string;

	/**
	 * @return mixed
	 */
	public function getValueForApi();
}
