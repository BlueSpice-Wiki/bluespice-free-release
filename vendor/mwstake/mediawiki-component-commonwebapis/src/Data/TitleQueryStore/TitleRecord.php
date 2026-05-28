<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs\Data\TitleQueryStore;

use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;
use MWStake\MediaWiki\Component\DataStore\IContinueAwareRecord;
use MWStake\MediaWiki\Component\DataStore\Record;

class TitleRecord extends Record implements IContinueAwareRecord {
	public const PAGE_ID = 'id';
	public const PAGE_TITLE = 'title';
	public const PAGE_DBKEY = 'dbkey';
	public const PAGE_PREFIXED = 'prefixed';
	public const PAGE_NAMESPACE = 'namespace';
	public const PAGE_DISPLAY_TITLE = 'display_title';
	public const PAGE_NAMESPACE_TEXT = 'namespace_text';
	public const PAGE_EXISTS = 'exists';
	public const PAGE_CONTENT_MODEL = 'page_content_model';
	public const PAGE_URL = 'url';
	public const IS_CONTENT_PAGE = 'is_content_page';
	public const PAGE_IS_REDIRECT = 'redirect';
	public const LEAF_TITLE = 'leaf_title';
	public const BASE_TITLE = 'base_title';
	public const SORTKEY = 'sortkey';

	/**
	 * @param TitleFactory $titleFactory
	 * @return Title|null
	 */
	public function getTitle( TitleFactory $titleFactory ): ?Title {
		return $titleFactory->makeTitleSafe(
			$this->get( self::PAGE_NAMESPACE ), $this->get( self::PAGE_DBKEY )
		);
	}

	/**
	 * @return array
	 */
	public function getContinueValue(): array {
		return [ $this->get( self::PAGE_NAMESPACE ), $this->get( self::PAGE_DBKEY ) ];
	}

	/**
	 * @param array $continueValue
	 * @return bool
	 */
	public function matchesContinueValue( array $continueValue ): bool {
		return count( $continueValue ) === 2 &&
			$this->get( self::PAGE_NAMESPACE ) === $continueValue[0] &&
			$this->get( self::PAGE_DBKEY ) === $continueValue[1];
	}
}
