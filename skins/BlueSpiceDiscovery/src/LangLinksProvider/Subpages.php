<?php

namespace BlueSpice\Discovery\LangLinksProvider;

use BlueSpice\Discovery\ILangLinksProvider;
use MediaWiki\Languages\LanguageNameUtils;
use MediaWiki\Page\PageProps;
use MediaWiki\Title\Title;

class Subpages implements ILangLinksProvider {

	/** @var LanguageNameUtils */
	private $languageNameUtils;

	/** @var PageProps */
	private $pageProps;

	/**
	 * @param LanguageNameUtils $languageNameUtils
	 * @param PageProps $pageProps
	 */
	public function __construct( LanguageNameUtils $languageNameUtils, PageProps $pageProps ) {
		$this->languageNameUtils = $languageNameUtils;
		$this->pageProps = $pageProps;
	}

	/**
	 * @param string[] $wikitextLangLinks
	 * @param Title $title
	 * @return string[]
	 */
	public function getLangLinks( array $wikitextLangLinks, Title $title ): array {
		if ( !$title->exists() ) {
			return [];
		}
		$availableLanguages = $this->getAvailableLanguages( $title );
		$links = [];
		foreach ( $availableLanguages as $code => $page ) {
			$langName = $this->languageNameUtils->getLanguageName( $code, $code );
			$links[] = [
				'href' => $page->getLocalURL(),
				'text' => $langName,
				'title' => $this->getDisplayText( $page ),
				'class' => 'interlanguage-link interwiki-' . $code,
				'link-class' => 'interlanguage-link-target',
				'lang' => $code,
				'hreflang' => $code
			];
		}
		return $links;
	}

	/**
	 * @param Title $titleObject
	 *
	 * @return array
	 */
	private function getAvailableLanguages( Title $titleObject ) {
		$available = [];
		$currentLangCode = $this->toLangCode( $titleObject->getSubpageText() );
		// Check if already on the language-subpage, if yes, get the parent to evaluate available langs
		if ( $currentLangCode ) {
			$parentTitle = $titleObject->getBaseTitle();
			$available[$parentTitle->getPageLanguage()->getCode()] = $parentTitle;
			$titleObject = $parentTitle;
		}

		if ( $titleObject->hasSubpages() ) {
			$subpages = $titleObject->getSubpages();
			foreach ( $subpages as $subpage ) {
				if ( !$subpage->exists() || $this->isNestedSubpage( $titleObject, $subpage ) ) {
					// Subpage does not exist or is a subpage of a subpage
					continue;
				}
				$subpageLangCode = $this->toLangCode( $subpage->getSubpageText() );
				if ( $subpageLangCode && ( !$currentLangCode || $subpageLangCode !== $currentLangCode ) ) {
					$available[$subpageLangCode] = $subpage;
				}
			}
		}
		return $available;
	}

	/**
	 * @param string $text
	 *
	 * @return string|null
	 */
	private function toLangCode( string $text ): ?string {
		$code = trim( strtolower( $text ) );
		if ( $this->languageNameUtils->isKnownLanguageTag( $code ) ) {
			if ( strlen( $code ) === 2 ) {
				return $code;
			}
		}
		return null;
	}

	/**
	 * @param Title $page
	 *
	 * @return string
	 */
	private function getDisplayText( Title $page ): string {
		$props = $this->pageProps->getProperties( $page, 'displaytitle' );
		if ( isset( $props[$page->getArticleID()] ) ) {
			return $props[$page->getArticleID()];
		}
		return $page->getText();
	}

	/**
	 * We only want to consider direct subpages of page we are visiting
	 * We want `A/en`, but not `A/B/en`
	 * @param Title $titleObject
	 * @param Title $subpage
	 *
	 * @return bool
	 */
	private function isNestedSubpage( Title $titleObject, Title $subpage ): bool {
		$fullText = $titleObject->getText();
		$subpageText = $subpage->getText();
		$diff = ltrim( str_replace( $fullText, '', $subpageText ), '/' );
		return strpos( $diff, '/' ) !== false;
	}
}
