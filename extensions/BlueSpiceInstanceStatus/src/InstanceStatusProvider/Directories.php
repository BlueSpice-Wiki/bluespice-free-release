<?php

namespace BlueSpice\InstanceStatus\InstanceStatusProvider;

use BlueSpice\InstanceStatus\IApiStatusProvider;
use Config;

class Directories implements IApiStatusProvider {

	/**
	 * @param Config $config
	 */
	public function __construct(
		private readonly Config $config
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function getKeyForApi(): string {
		return 'directories-writable';
	}

	/**
	 * @inheritDoc
	 */
	public function getValueForApi() {
		$errors = [];
		if ( !is_writable( $this->config->get( 'UploadDirectory' ) ) ) {
			$errors[] = '$wgUploadDirectory not writable';
		}
		if ( !is_writable( $this->config->get( 'CacheDirectory' ) ) ) {
			$errors[] = '$wgCacheDirectory not writable';
		}
		if ( $errors ) {
			return implode( '; ', $errors );
		}
		return 'OK';
	}
}
