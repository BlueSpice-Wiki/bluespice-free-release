<?php

namespace BlueSpice\InstanceStatus\InstanceStatusProvider;

use BlueSpice\InstanceStatus\IApiStatusProvider;
use BlueSpice\InstanceStatus\IStatusProvider;
use MediaWiki\Message\Message;
use MediaWiki\Parser\Sanitizer;

class BlueSpiceVersion implements IStatusProvider, IApiStatusProvider {

	/**
	 * @return string
	 */
	public function getLabel(): string {
		return Message::newFromKey( 'bs-instancestatus-instance-status-bs-version-label' )->text();
	}

	/**
	 * @return string
	 */
	public function getValue(): string {
		$version = '';
		$versionFile = $GLOBALS['IP'] . '/BLUESPICE-VERSION';
		if ( file_exists( $versionFile ) ) {
			$versionFileContent = file_get_contents( $versionFile );
			$version = Sanitizer::stripAllTags( $versionFileContent );
		}
		return $version;
	}

	/**
	 * @return string
	 */
	public function getIcon(): string {
		return 'bluespice';
	}

	/**
	 * @return int
	 */
	public function getPriority(): int {
		return 30;
	}

	/**
	 * @inheritDoc
	 */
	public function getKeyForApi(): string {
		return 'bs-version';
	}

	/**
	 * @inheritDoc
	 */
	public function getValueForApi() {
		return $this->getValue();
	}
}
