<?php

namespace BlueSpice\InsertCategory\MetaItemProvider;

use BlueSpice\Discovery\IMetaItemProvider;
use BlueSpice\InsertCategory\InsertCategoryTool;
use MWStake\MediaWiki\Component\CommonUserInterface\IComponent;

class Categories implements IMetaItemProvider {

	/**
	 *
	 * @inheritDoc
	 */
	public function getName(): string {
		return 'categories';
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function getComponent(): IComponent {
		return new InsertCategoryTool();
	}
}
