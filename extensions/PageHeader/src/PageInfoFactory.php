<?php

namespace PageHeader;

use LogicException;
use MediaWiki\Config\Config;
use MediaWiki\Context\IContextSource;
use MWStake\MediaWiki\Component\ManifestRegistry\IRegistry;

class PageInfoFactory {

	/**
	 *
	 * @var IPageInfo[]
	 */
	private $instances = [];

	/**
	 *
	 * @var IRegistry
	 */
	private $registry = null;

	/**
	 *
	 * @var Config
	 */
	private $config = null;

	/**
	 *
	 * @param IRegistry $registry
	 * @param Config $config
	 */
	public function __construct( IRegistry $registry, Config $config ) {
		$this->registry = $registry;
		$this->config = $config;
	}

	/**
	 * @param IContextSource $context
	 * @return PageInfo[]
	 * @throws LogicException
	 */
	public function getAll( IContextSource $context ) {
		if ( !$context->getTitle() ) {
			throw new LogicException(
				"No Title in Context"
			);
		}
		if ( isset( $this->instances[$context->getTitle()->getFullText()] ) ) {
			return $this->instances[$context->getTitle()->getFullText()];
		}
		$this->instances[$context->getTitle()->getFullText()] = [];
		foreach ( $this->registry->getAllKeys() as $key ) {
			$callback = $this->registry->getValue( $key );
			if ( !is_callable( $callback ) ) {
				continue;
			}
			$instance = call_user_func_array(
				$callback,
				[ $context, $this->config ]
			);
			if ( !$instance instanceof IPageInfo ) {
				continue;
			}
			$this->instances[$context->getTitle()->getFullText()][$key] = $instance;
		}
		foreach ( $this->getLegacyRegistry( $context ) as $key => $legacy ) {
			if ( isset( $this->instances[$context->getTitle()->getFullText()][$key] ) ) {
				continue;
			}
			$this->instances[$context->getTitle()->getFullText()][$key] = $legacy;
		}

		return $this->instances[$context->getTitle()->getFullText()];
	}

	/**
	 *
	 * @param string $key
	 * @param IContextSource $context
	 * @return IPageInfo|null
	 */
	public function get( $key, IContextSource $context ) {
		return isset( $this->getAll( $context )[$key] ) ? $this->getAll( $context )[$key] : null;
	}

	/**
	 * @param IContextSource $context
	 * @return IRegistry
	 */
	private function getLegacyRegistry( IContextSource $context ) {
		$return = [];
		$registry = \MediaWiki\MediaWikiServices::getInstance()
			->getService( 'MWStakeManifestRegistryFactory' )
			->get( 'BlueSpiceFoundationPageInfoElementRegistry' );
		if ( !empty( $registry->getAllKeys() ) ) {
			// TODO: Deprecation waring!
		}
		foreach ( $registry->getAllKeys() as $key ) {
			$callback = $registry->getValue( $key );
			if ( !is_callable( $callback ) ) {
				continue;
			}
			$instance = call_user_func_array(
				$callback,
				[ $context, $this->config ]
			);
			if ( !$instance ) {
				continue;
			}
			$return[$key] = $instance;
		}
		return $return;
	}
}
