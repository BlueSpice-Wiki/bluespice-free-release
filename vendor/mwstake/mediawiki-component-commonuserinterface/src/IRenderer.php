<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

interface IRenderer {

	/**
	 *
	 * @param IComponent $component
	 * @return bool
	 */
	public function canRender( IComponent $component ): bool;

	/**
	 * Having this public should enable client-side rendering
	 *
	 * @param IComponent $component
	 * @return array
	 */
	public function getTemplateData( IComponent $component ): array;

	/**
	 * Having this public should enable client-side rendering
	 *
	 * @return string
	 */
	public function getTemplatePathname(): string;

	/**
	 *
	 * @param IComponent $component
	 * @return string
	 */
	public function render( IComponent $component ): string;

	/**
	 *
	 * @return array
	 */
	public function getRLModules(): array;

		/**
		 *
		 * @return array
		 */
	public function getRLModuleStyles(): array;
}
