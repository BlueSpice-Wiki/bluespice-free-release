<?php

namespace BlueSpice\Discovery;

use MediaWiki\Context\IContextSource;
use MediaWiki\Registration\ExtensionRegistry;
use Wikimedia\ObjectFactory\ObjectFactory;

class BackLinkProviderFactory {

	/** @var ObjectFactory */
	private $objectFactory;

	/**
	 *
	 * @param ObjectFactory $objectFactory
	 */
	public function __construct( ObjectFactory $objectFactory ) {
		$this->objectFactory = $objectFactory;
	}

	/**
	 *
	 * @param IContextSource $context
	 * @return IBackLinkProvider|null
	 */
	public function getProvider( $context ) {
		$backLinkOptions = ExtensionRegistry::getInstance()->getAttribute(
			'BlueSpiceDiscoveryBackLinkProviderRegistry'
		);
		foreach ( $backLinkOptions as $key => $spec ) {
			$backLinkProvider = $this->objectFactory->createObject( $spec );

			if ( $backLinkProvider->applies( $context ) ) {
				return $backLinkProvider;
			}
		}
		return null;
	}
}
