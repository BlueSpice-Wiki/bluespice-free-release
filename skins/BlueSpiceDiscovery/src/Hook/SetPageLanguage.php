<?php

namespace BlueSpice\Discovery\Hook;

use MediaWiki\Context\RequestContext;
use MediaWiki\Languages\LanguageNameUtils;
use MediaWiki\Storage\Hook\PageSaveCompleteHook;
use SpecialPageLanguage;

class SetPageLanguage implements PageSaveCompleteHook {

	/**
	 * @var LanguageNameUtils
	 */
	private $languageNameUtils;

	/**
	 * @param LanguageNameUtils $languageNameUtils
	 */
	public function __construct( LanguageNameUtils $languageNameUtils ) {
		$this->languageNameUtils = $languageNameUtils;
	}

	/**
	 * @inheritDoc
	 */
	public function onPageSaveComplete( $wikiPage, $user, $summary, $flags, $revisionRecord, $editResult ) {
		if ( !( $flags & EDIT_NEW ) ) {
			return;
		}
		if (
			$wikiPage->getTitle()->getContentModel() !== CONTENT_MODEL_WIKITEXT ||
			!$wikiPage->getTitle()->isSubpage()
		) {
			return;
		}
		$maybeCode = $code = trim( strtolower( $wikiPage->getTitle()->getSubpageText() ) );
		if ( !$this->languageNameUtils->isKnownLanguageTag( $code ) ) {
			return;
		}
		SpecialPageLanguage::changePageLanguage( RequestContext::getMain(), $wikiPage->getTitle(), $maybeCode );
	}
}
