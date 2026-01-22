<?php

namespace BlueSpice\WatchList\Tag;

use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\FormEngine\StandaloneFormSpecification;
use MWStake\MediaWiki\Component\GenericTagHandler\ClientTagSpecification;
use MWStake\MediaWiki\Component\GenericTagHandler\GenericTag;
use MWStake\MediaWiki\Component\GenericTagHandler\ITagHandler;
use MWStake\MediaWiki\Component\GenericTagHandler\MarkerType;
use MWStake\MediaWiki\Component\InputProcessor\Processor\IntValue;
use MWStake\MediaWiki\Component\InputProcessor\Processor\KeywordValue;

class Watchlist extends GenericTag {

	/**
	 * @inheritDoc
	 */
	public function getTagNames(): array {
		return [
			'bs:watchlist',
			'watchlist',
		];
	}

	/**
	 * @return bool
	 */
	public function hasContent(): bool {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function getMarkerType(): MarkerType {
		return new MarkerType\NoWiki();
	}

	/**
	 * @inheritDoc
	 */
	public function getContainerElementName(): ?string {
		return 'div';
	}

	/**
	 * @inheritDoc
	 */
	public function getHandler( MediaWikiServices $services ): ITagHandler {
		return new WatchlistHandler(
			$services->getDBLoadBalancer(),
			$services->getTitleFactory()
		);
	}

	/**
	 * @inheritDoc
	 */
	public function getParamDefinition(): ?array {
		$count = ( new IntValue() )
			->setMin( 1 )
			->setMax( 1000 )
			->setDefaultValue( 5 );
		$titleLength = ( new IntValue() )
			->setDefaultValue( 20 )
			->setMin( 5 )
			->setMax( 500 );
		$order = ( new KeywordValue() )
			->setKeywords( [
				'pagename',
				'time'
			] )
			->setDefaultValue( 'time' );

		return [
			'count' => $count,
			'maxtitlelength' => $titleLength,
			'order' => $order,
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getClientTagSpecification(): ClientTagSpecification|null {
		$formSpec = new StandaloneFormSpecification();
		$formSpec->setItems( [
			[
				'type' => 'number',
				'name' => 'count',
				'label' => Message::newFromKey( 'bs-watchlist-ve-watchlist-attr-number-result-label' )->text(),
				'help' => Message::newFromKey( 'bs-watchlist-tag-watchlist-attr-count-help' )->text(),
				'value' => 5,
				'widget_min' => 1,
				'widget_max' => 1000
			],
			[
				'type' => 'number',
				'name' => 'maxtitlelength',
				'label' => Message::newFromKey( 'bs-watchlist-ve-watchlist-attr-titlelength-label' )->text(),
				'help' => Message::newFromKey( 'bs-watchlist-tag-watchlist-attr-maxtitlelength-help' )->text(),
				'value' => 20,
				'widget_min' => 5,
				'widget_max' => 500
			],
			[
				'type' => 'dropdown',
				'name' => 'order',
				'label' => Message::newFromKey( 'bs-watchlist-ve-watchlist-attr-sort-by-label' )->text(),
				'help' => Message::newFromKey( 'bs-watchlist-tag-watchlist-attr-order-help' )->text(),
				'value' => 'time',
				'options' => [
					[
						'data' => 'pagename',
						'label' => Message::newFromKey( 'bs-watchlist-ve-watchlist-attr-pagename-label' )->text()
					],
					[
						'data' => 'time',
						'label' => Message::newFromKey( 'bs-watchlist-ve-watchlist-attr-time-label' )->text()
					]
				]
			]
		] );

		return new ClientTagSpecification(
			'Watchlist',
			Message::newFromKey( 'bs-watchlist-tag-watchlist-desc' ),
			$formSpec,
			Message::newFromKey( 'bs-watchlist-ve-watchlistinspector-title' )
		);
	}
}
