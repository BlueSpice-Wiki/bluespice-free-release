<?php

namespace BlueSpice\Discovery\LangLinksProvider;

use BlueSpice\Discovery\ILangLinksProvider;
use MediaWiki\Config\ConfigFactory;
use MediaWiki\Languages\LanguageNameUtils;
use MediaWiki\Title\Title;

class Hardwired implements ILangLinksProvider {

	/** @var LanguageNameUtils */
	private $languageNameUtils = null;

	/** @var ConfigFactory */
	private $configFactory = null;

	/**
	 * @param LanguageNameUtils $languageNameUtils
	 * @param ConfigFactory $configFactory
	 */
	public function __construct( LanguageNameUtils $languageNameUtils, ConfigFactory $configFactory ) {
		$this->languageNameUtils = $languageNameUtils;
		$this->configFactory = $configFactory;
	}

	/**
	 * @param string[] $wikitextLangLinks
	 * @param Title $title
	 * @return string[]
	 */
	public function getLangLinks( array $wikitextLangLinks, Title $title ): array {
		$config = $this->configFactory->makeConfig( 'bsg' );
		$hardLangLinks = $config->get( 'DiscoveryHardWiredLangLinks' );

		$titleText = $title->getPrefixedDBKey();
		$links = [];

		foreach ( $hardLangLinks as $hardLang => $hardLink ) {
			$href = str_replace( '$1', $titleText, $hardLink );
			$langName = $this->languageNameUtils->getLanguageName( $hardLang );

			array_push( $links,
				[
					'href' => $href,
					'text' => $langName,
					'title' => $title->getPrefixedText() . ' – ' . $langName,
					'class' => 'interlanguage-link interwiki-' . $hardLang,
					'link-class' => 'interlanguage-link-target',
					'lang' => $hardLang,
					'hreflang' => $hardLang
				]
			);
		}
		return $links;
	}
}
