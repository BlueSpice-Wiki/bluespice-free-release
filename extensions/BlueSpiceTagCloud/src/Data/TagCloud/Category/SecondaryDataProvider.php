<?php

namespace BlueSpice\TagCloud\Data\TagCloud\Category;

use BlueSpice\TagCloud\Data\TagCloud\Record;
use MediaWiki\Context\IContextSource;
use MediaWiki\Title\Title;

class SecondaryDataProvider extends \MWStake\MediaWiki\Component\DataStore\SecondaryDataProvider {

	/**
	 *
	 * @var \MediaWiki\Linker\LinkRenderer
	 */
	protected $linkrenderer = null;

	/**
	 * @var IContextSource
	 */
	protected $context;

	/**
	 *
	 * @param \MediaWiki\Linker\LinkRenderer $linkrenderer
	 * @param IContextSource $context
	 */
	public function __construct( $linkrenderer, $context ) {
		$this->linkrenderer = $linkrenderer;
		$this->context = $context;
	}

	/**
	 *
	 * @param Record &$dataSet
	 */
	protected function doExtend( &$dataSet ) {
		$rawData = $dataSet->getData();

		$title = Title::newFromText( $rawData->{Record::NAME}, NS_CATEGORY );
		$rawData->{Record::RENDEREDLINK} = $this->linkrenderer->makeLink(
			$title,
			new \HtmlArmor( $title->getText() )
		);
		$rawData->{Record::LINK} = $title->getLocalURL();

		$dataSet = new Record( $rawData );
	}
}
