<?php

namespace MediaWiki\Extension\ContentDroplets;

use MediaWiki\Message\Message;

interface IDropletDescription {
	/**
	 * @return Message
	 */
	public function getName(): Message;

	/**
	 * @return Message
	 */
	public function getDescription(): Message;

	/**
	 * @return string
	 */
	public function getIcon(): string;

	/**
	 * @return array
	 */
	public function getRLModules(): array;

	/**
	 * @return array
	 */
	public function getCategories(): array;

	/**
	 * @return string
	 */
	public function getContent(): string;

	/**
	 * Name of the VE insert command
	 * @return string|null
	 */
	public function getVeCommand(): ?string;
}
