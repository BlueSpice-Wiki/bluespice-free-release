<?php

namespace MediaWiki\Extension\UserProfile\ProfileImage;

use InvalidArgumentException;

class ProfileImageProviderFactory {

	/** @var array */
	private array $providers = [];

	/**
	 * @param string $providerName
	 * @return IProfileImageProvider
	 */
	public function getProvider( string $providerName ): IProfileImageProvider {
		if ( !isset( $this->providers[$providerName] ) ) {
			throw new InvalidArgumentException( "Unknown provider: $providerName" );
		}
		return $this->providers[$providerName];
	}

	/**
	 * @return IProfileImageProvider[]
	 */
	public function getAll(): array {
		// Order by `getPriority` method
		uasort( $this->providers, static function ( IProfileImageProvider $a, IProfileImageProvider $b ) {
			return $a->getPriority() <=> $b->getPriority();
		} );
		return $this->providers;
	}

	/**
	 * @param string $name
	 * @param IProfileImageProvider $provider
	 * @return void
	 */
	public function registerProvider( string $name, IProfileImageProvider $provider ) {
		$this->providers[$name] = $provider;
	}
}
