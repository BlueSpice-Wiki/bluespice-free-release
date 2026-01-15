<?php

namespace MediaWiki\Extension\MenuEditor\HookHandler;

use MediaWiki\Output\Hook\BeforePageDisplayHook;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;

class AddFooterLinksBanner implements BeforePageDisplayHook {

	/** @var Title */
	private $footerLinksTitle;

	/**
	 * @param TitleFactory $titleFactory
	 */
	public function __construct( TitleFactory $titleFactory ) {
		$this->footerLinksTitle = $titleFactory->newFromText( 'FooterLinks', NS_MEDIAWIKI );
	}

	/**
	 * @inheritDoc
	 */
	public function onBeforePageDisplay( $out, $skin ): void {
		if ( $out->getTitle() && $out->getTitle()->equals( $this->footerLinksTitle ) ) {
			$out->addModules( [ 'ext.menuEditor.footerlinks.banner' ] );
		}
	}
}
