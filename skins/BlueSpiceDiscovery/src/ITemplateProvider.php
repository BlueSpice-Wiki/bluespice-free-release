<?php

namespace BlueSpice\Discovery;

interface ITemplateProvider {

	/**
	 * @return string
	 */
	public function getTemplateName(): string;

	/**
	 * @return string
	 */
	public function getTemplatePath(): string;
}
