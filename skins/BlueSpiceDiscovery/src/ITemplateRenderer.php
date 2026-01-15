<?php

namespace BlueSpice\Discovery;

interface ITemplateRenderer {

	/**
	 *
	 * @return string
	 */
	public function getHtml(): string;

	/**
	 *
	 * @return array
	 */
	public function getParams(): array;

	/**
	 *
	 * @return string
	 */
	public function getTemplatePath(): string;
}
