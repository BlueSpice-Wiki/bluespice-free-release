<?php

namespace MediaWiki\Extension\PDFCreator\Factory;

use MediaWiki\Extension\PDFCreator\IPageParamsProvider;
use MediaWiki\Page\PageIdentity;
use MediaWiki\Registration\ExtensionRegistry;
use MediaWiki\User\UserIdentity;
use Wikimedia\ObjectFactory\ObjectFactory;

class PageParamsFactory {

	/** @var ObjectFactory */
	private $objectFactory;

	/** @var array */
	private $registry;

	/**
	 * @param ObjectFactory $objectFactory
	 */
	public function __construct( ObjectFactory $objectFactory ) {
		$this->objectFactory = $objectFactory;
		$this->registry = ExtensionRegistry::getInstance()->getAttribute(
			'PDFCreatorPageParamsProvider'
		);
	}

	/**
	 * @param PageIdentity $pageIdentity
	 * @param UserIdentity|null $userIdentity
	 * @return array
	 */
	public function getParams( PageIdentity $pageIdentity, ?UserIdentity $userIdentity ): array {
		$AllParams = [];
		foreach ( $this->registry as $name => $spec ) {
			$provider = $this->objectFactory->createObject( $spec );
			if ( $provider instanceof IPageParamsProvider === false ) {
				continue;
			}
			$params = $provider->execute( $pageIdentity, $userIdentity );
			$AllParams = array_merge( $AllParams, $params );
		}

		return $AllParams;
	}

	/**
	 * @param string $lang
	 * @return array
	 */
	public function getParamDescription( $lang = 'en' ): array {
		$desc = [];
		foreach ( $this->registry as $name => $spec ) {
			$provider = $this->objectFactory->createObject( $spec );
			if ( $provider instanceof IPageParamsProvider === false ) {
				continue;
			}
			$params = $provider->getParamsDescription( $lang );
			$desc = array_merge( $desc, $params );
		}

		return $desc;
	}
}
