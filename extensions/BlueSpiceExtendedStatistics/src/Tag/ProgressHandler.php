<?php

namespace BlueSpice\ExtendedStatistics\Tag;

use BlueSpice\Tag\Handler;
use BsPageContentProvider;
use MediaWiki\Context\RequestContext;
use MediaWiki\Html\Html;
use MediaWiki\Message\Message;

class ProgressHandler extends Handler {

	/**
	 * @return string
	 */
	public function handle() {
		$this->parser->getOutput()->setPageProperty( 'bs-tag-statistics-progress', 1 );
		$iBaseCount = $this->processedArgs[Progress::ATTR_BASE_COUNT];
		$sBaseItem  = $this->processedArgs[Progress::ATTR_BASE_ITEM];
		$sFraction  = $this->processedArgs[Progress::ATTR_PROGRESS_ITEM];
		$iWidth     = $this->processedArgs[Progress::ATTR_WIDTH];

		// no Article when in cli mode
		if ( !RequestContext::getMain()->getTitle() ) {
			return '';
		}

		$sText = BsPageContentProvider::getInstance()->getContentFromTitle(
			RequestContext::getMain()->getTitle()
		);

		// substract 1 because one item is in the progressitem attribute
		$iFraction = substr_count( $sText, $sFraction ) - 1;

		if ( $sBaseItem ) {
			$iBase = substr_count( $sText, $sBaseItem ) - 1;
		} else {
			$iBase = $iBaseCount;
		}

		$fPercent = $iFraction / $iBase;

		$iWidthGreen = floor( $iWidth * $fPercent );
		$iWidthRemain = $iWidth - $iWidthGreen;

		$sPercent = sprintf( "%0.1f", $fPercent * 100 );
		$sBar = Html::rawElement(
			'div',
			[
				'class' => 'progress',
				'style' => 'height: 25px; width:' . $iWidth . 'px;',
			],
			Html::element(
				'div',
				[
					'class' => 'progress-bar bg-success',
					'style' => 'width:' . $sPercent . '%;',
				],
				$sPercent . '%'
			)
		);
		$sOut = Message::newFromKey( 'bs-statistics-tag-progress-label-text', $sBar )->text();
		return $sOut;
	}
}
