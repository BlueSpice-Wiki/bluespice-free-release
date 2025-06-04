<?php

namespace MediaWiki\Extension\FlexiSkin;

interface IFlexiSkinManager {
	/**
	 * @param string $name
	 * @param array $config
	 * @return IFlexiSkin
	 */
	public function create( $name, $config ): IFlexiSkin;

	/**
	 * @param IFlexiSkin $flexiSkin
	 * @return bool
	 */
	public function save( IFlexiSkin $flexiSkin );

	/**
	 * @param string $skinname
	 * @return IFlexiSkin|null
	 */
	public function getFlexiSkin( $skinname = '' ): ?IFlexiSkin;

	/**
	 * @param IFlexiSkin|null $flexiSkin
	 * @param bool|null $active
	 * @return bool
	 */
	public function setActive( ?IFlexiSkin $flexiSkin = null, $active = true ): bool;

	/**
	 * @param string $skinname
	 * @return IFlexiSkin|null
	 */
	public function getActive( $skinname = '' ): ?IFlexiSkin;

	/**
	 * Get all available plugins
	 *
	 * @return IPlugin[]
	 */
	public function getPlugins(): array;

	/**
	 * @param string $skinname
	 * @return array
	 */
	public function getActiveConfig( $skinname = '' ): array;
}
