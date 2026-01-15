<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

use MediaWiki\Content\CssContent;
use MediaWiki\Revision\RevisionLookup;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Title\TitleFactory;

class MediaWikiCommonCssProvider {

	/** @var TitleFactory */
	private $titleFactory;

	/** @var RevisionLookup */
	private $revisionLookup;

	/**
	 * @param TitleFactory $titleFactory
	 * @param RevisionLookup $revisionLookup
	 */
	public function __construct( TitleFactory $titleFactory, RevisionLookup $revisionLookup ) {
		$this->titleFactory = $titleFactory;
		$this->revisionLookup = $revisionLookup;
	}

	/**
	 * @return string
	 */
	public function getStyles(): string {
		$title = $this->titleFactory->newFromText( 'Common.css', NS_MEDIAWIKI );
		$revision = $this->revisionLookup->getRevisionByTitle( $title );
		if ( !$revision ) {
			return '';
		}

		$css = '';
		$content = $revision->getContent( SlotRecord::MAIN );
		if ( $content instanceof CssContent ) {
			$css = $content->getText();
		}

		return $css;
	}
}
