<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

use MediaWiki\Context\IContextSource;

interface IComponent {

	/**
	 *
	 * @return string
	 */
	public function getId(): string;

	/**
	 *
	 * @return string[]
	 */
	public function getRequiredRLModules(): array;

	/**
	 *
	 * @return string[]
	 */
	public function getRequiredRLStyles(): array;

	/**
	 * @return IComponent[]
	 */
	public function getSubComponents(): array;

	/**
	 *
	 * @param IContextSource $context
	 * @return bool
	 */
	public function shouldRender( IContextSource $context ): bool;

	/**
	 *
	 * @param array $data Arbitrary data to be consumed by the components.
	 *                    Usually this is SkinTemplate's `$tpl->data`
	 * @return void
	 */
	public function setComponentData( $data ): void;
}
