<?php

namespace BlueSpice\InsertCategory\Content;

use MediaWiki\Content\JsonContentHandler;

class CategoryContentHandler extends JsonContentHandler {

	/**
	 * @param string $modelId
	 */
	public function __construct( $modelId = 'category_storage' ) {
		parent::__construct( $modelId );
	}

	/**
	 * @return string
	 */
	protected function getContentClass() {
		return CategoryContent::class;
	}
}
