<?php

namespace BlueSpice\ExtendedStatistics\Tag;

use BsPageContentProvider;
use MediaWiki\Html\Html;
use MediaWiki\Message\Message;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;
use MediaWiki\Title\Title;
use MWStake\MediaWiki\Component\GenericTagHandler\ITagHandler;

class ProgressHandler implements ITagHandler {

	/**
	 * @param BsPageContentProvider $pageContentProvider
	 * @param Title|null $title
	 */
	public function __construct(
		private readonly BsPageContentProvider $pageContentProvider,
		private readonly ?Title $title
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function getRenderedContent( string $input, array $params, Parser $parser, PPFrame $frame ): string {
		$parser->getOutput()->setPageProperty( 'bs-tag-statistics-progress', 1 );

		// no Article when in cli mode
		if ( !$this->title ) {
			return '';
		}

		$text = $this->pageContentProvider->getContentFromTitle( $this->title );

		// substract 1 because one item is in the progressitem attribute
		$fraction = substr_count( $text, $params['progressitem'] ) - 1;

		if ( $params['baseitem'] ) {
			$base = substr_count( $text, $params['baseitem'] ) - 1;
		} else {
			$base = $params['basecount'];
		}

		$percent = $fraction / $base;
		if ( $percent < 0 ) {
			$percent = 0;
		}
		$percent = sprintf( "%0.1f", $percent * 100 );
		$bar = Html::rawElement(
			'div',
			[
				'class' => 'progress',
				'style' => 'height: 25px; width:' . $params['width'] . 'px;',
			],
			Html::element(
				'div',
				[
					'class' => 'progress-bar bg-success',
					'style' => 'width:' . $percent . '%;',
				],
				$percent . '%'
			)
		);
		return Message::newFromKey( 'bs-statistics-tag-progress-label-text', $bar )->text();
	}
}
