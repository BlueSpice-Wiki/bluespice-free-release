<?php

namespace MediaWiki\Extension\FlexiSkin;

use MediaWiki\Config\Config;
use MediaWiki\MediaWikiServices;

class FlexiSkinConfig implements Config {
	/** @var array */
	private $config = [];

	/** @var bool */
	private $initialized = false;

	/** @var array */
	private $supportedSettings = [
		'Logo',
		'Logos',
		'Favicon'
	];

	private function init() {
		if ( $this->initialized ) {
			return;
		}

		$services = MediaWikiServices::getInstance();
		/** @var IFlexiSkinManager $flexiSkinManager */
		$flexiSkinManager = $services->get( 'FlexiSkinManager' );
		$active = $flexiSkinManager->getActive();
		if ( !$active instanceof IFlexiSkin ) {
			$this->initialized = true;
			return;
		}

		$config = $flexiSkinManager->getActiveConfig();
		$logoUrl = $config['images']['logo']['url'] ?? null;
		if ( $logoUrl ) {
			$this->config['Logos'] = [
				'1x' => $logoUrl
			];
			$this->config['Logo'] = $logoUrl;
		}

		$faviconUrl = $config['images']['favicon']['url'] ?? null;
		if ( $faviconUrl ) {
			$this->config['Favicon'] = $faviconUrl;
		}

		$this->initialized = true;
	}

	/**
	 * @inheritDoc
	 */
	public function get( $name ) {
		$this->init();
		return $this->config[$name] ?? null;
	}

	/**
	 * @inheritDoc
	 */
	public function has( $name ) {
		if ( !in_array( $name, $this->supportedSettings ) ) {
			return false;
		}

		$this->init();
		return isset( $this->config[$name] );
	}
}
