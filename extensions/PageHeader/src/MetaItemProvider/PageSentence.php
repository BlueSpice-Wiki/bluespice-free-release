<?php

namespace PageHeader\MetaItemProvider;

use BlueSpice\Discovery\IMetaItemProvider;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\CommonUserInterface\IComponent;
use PageHeader\Component\Literal\PageInfoSentence;

class PageSentence implements IMetaItemProvider {

	/**
	 *
	 * @return string
	 */
	public function getName(): string {
		return 'page-sentence';
	}

	/**
	 *
	 * @return IComponent
	 */
	public function getComponent(): IComponent {
		$builder = MediaWikiServices::getInstance()->get( 'PageInfoSentenceBuilder' );
		return new PageInfoSentence( $builder );
	}
}
