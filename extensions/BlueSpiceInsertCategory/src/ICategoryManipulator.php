<?php

namespace BlueSpice\InsertCategory;

use MediaWiki\Page\PageIdentity;
use MediaWiki\Permissions\Authority;
use MediaWiki\Revision\RevisionRecord;

interface ICategoryManipulator {
	/**
	 * @param PageIdentity $pageIdentity
	 * @param RevisionRecord|null $revisionRecord
	 * @return array
	 */
	public function getCategories( PageIdentity $pageIdentity, ?RevisionRecord $revisionRecord = null ): array;

	/**
	 * @param PageIdentity $pageIdentity
	 * @param array $categoryTitles
	 * @param Authority $actor
	 * @return bool
	 */
	public function setCategories( PageIdentity $pageIdentity, array $categoryTitles, Authority $actor ): bool;
}
