<?php

namespace BlueSpice\InsertCategory\Hook;

use BlueSpice\InsertCategory\ICategoryManipulator;
use MediaWiki\Content\Content;
use MediaWiki\Title\Title;

interface BlueSpiceInsertCategoryGetCategoryManipulatorHook {
	/**
	 * Set correct category manipulator for given Content
	 * Set to null if no categorization is supported
	 *
	 * @param Content $content
	 * @param Title $page
	 * @param ICategoryManipulator &$manipulator
	 * @return void
	 */
	public function onBlueSpiceInsertCategoryGetCategoryManipulator(
		Content $content,
		Title $page,
		ICategoryManipulator &$manipulator
	): void;
}
