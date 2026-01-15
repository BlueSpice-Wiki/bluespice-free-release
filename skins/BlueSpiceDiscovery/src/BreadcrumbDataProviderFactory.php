<?php

namespace BlueSpice\Discovery;

use BlueSpice\Discovery\BreadcrumbDataProvider\BaseBreadcrumbDataProvider;
use MediaWiki\Registration\ExtensionRegistry;
use MediaWiki\Title\NamespaceInfo;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;
use MediaWiki\User\User;
use Wikimedia\ObjectFactory\ObjectFactory;

class BreadcrumbDataProviderFactory {

	/**
	 *
	 * @var array
	 */
	private $webRequestValues;

	/**
	 *
	 * @var MessageLocalizer
	 */
	private $messageLocalizer;

	/**
	 *
	 * @var TitleFactory
	 */
	private $titleFactory;

	/** @var ObjectFactory */
	private $objectFactory;

	/**
	 *
	 * @var NamespaceInfo
	 */
	private $namespaceInfo = null;

	/**
	 *
	 * @param MessageLocalizer $messageLocalizer
	 * @param array $webRequestValues
	 * @param TitleFactory $titleFactory
	 * @param NamespaceInfo $namespaceInfo
	 * @param ObjectFactory $objectFactory
	 */
	public function __construct( $messageLocalizer, $webRequestValues, $titleFactory,
		$namespaceInfo, $objectFactory ) {
		$this->messageLocalizer = $messageLocalizer;
		$this->webRequestValues = $webRequestValues;
		$this->titleFactory = $titleFactory;
		$this->namespaceInfo = $namespaceInfo;
		$this->objectFactory = $objectFactory;
	}

	/**
	 * @param Title $title
	 * @param User $user
	 * @return BaseBreadcrumbDataProvider
	 */
	public function getProviderForTitle( $title, $user ): BaseBreadcrumbDataProvider {
		$providers = ExtensionRegistry::getInstance()->getAttribute(
			'BlueSpiceDiscoveryBreadcrumbDataProviderRegistry'
		);
		$args = [
			$this->titleFactory, $this->messageLocalizer, $this->webRequestValues, $this->namespaceInfo
		];
		foreach ( $providers as $key => $spec ) {
			$spec = array_merge( $spec, [ 'args' => $args ] );
			$provider = $this->objectFactory->createObject( $spec );
			if ( !( $provider instanceof IBreadcrumbDataProvider ) ) {
				continue;
			}
			if ( $provider->applies( $title ) ) {
				return $provider;
			}
		}

		return $this->objectFactory->createObject( [
			'class' => BaseBreadcrumbDataProvider::class,
			'args' => $args,
		] );
	}
}
