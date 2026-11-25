<?php

namespace MediaWiki\Extension\EnhancedStandardUIs\ConfigDefinition;

use BlueSpice\ConfigDefinition\ArraySetting;
use BlueSpice\ConfigDefinition\IOverwriteGlobal;
use MediaWiki\Config\Config;
use MediaWiki\Context\IContextSource;
use MediaWiki\Language\Language;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\NamespaceInfo;

class EnhancedUIsAllPagesExcludedNamespaces extends ArraySetting implements IOverwriteGlobal {

	/**
	 * @inheritDoc
	 */
	public static function getInstance( $context, $config, $name ) {
		return new static(
			$context, $config, $name,
			MediaWikiServices::getInstance()->getNamespaceInfo(),
			MediaWikiServices::getInstance()->getContentLanguage()
		);
	}

	/**
	 * @param IContextSource $context
	 * @param Config $config
	 * @param string $name
	 * @param NamespaceInfo $namespaceInfo
	 * @param Language $language
	 */
	public function __construct(
		$context, $config, $name,
		private readonly NamespaceInfo $namespaceInfo,
		private readonly Language $language
	) {
		parent::__construct( $context, $config, $name );
	}

	/**
	 * @return string[]
	 */
	public function getPaths() {
		return [
			static::MAIN_PATH_FEATURE . '/' . static::FEATURE_SKINNING . '/EnhancedStandardUIs',
			static::MAIN_PATH_EXTENSION . '/EnhancedStandardUIs/' . static::FEATURE_SKINNING,
			static::MAIN_PATH_PACKAGE . '/' . static::PACKAGE_FREE . '/EnhancedStandardUIs',
		];
	}

	/**
	 * @return array
	 */
	protected function getOptions() {
		$content = $this->namespaceInfo->getValidNamespaces();

		$options = [];
		foreach ( $content as $ns ) {
			$options[$ns] = $ns === NS_MAIN ?
				$this->context->msg( 'enhanced-standard-uis-watchlist-main-namespace-label' )->text() :
				$this->language->getNsText( $ns );
		}
		return $options;
	}

	/**
	 * @return string
	 */
	public function getGlobalName() {
		return 'wgEnhancedUIsAllPagesExcludedNamespaces';
	}

	/**
	 * @return void
	 */
	public function getLabelMessageKey() {
		return 'enhanced-standard-uis-allpages-exclude-namespaces-label';
	}
}
