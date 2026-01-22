<?php

namespace BlueSpice\PagesVisited\Tag;

use MediaWiki\Language\Language;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\Title\NamespaceInfo;
use MWStake\MediaWiki\Component\FormEngine\StandaloneFormSpecification;
use MWStake\MediaWiki\Component\GenericTagHandler\ClientTagSpecification;
use MWStake\MediaWiki\Component\GenericTagHandler\GenericTag;
use MWStake\MediaWiki\Component\GenericTagHandler\ITagHandler;
use MWStake\MediaWiki\Component\GenericTagHandler\MarkerType;
use MWStake\MediaWiki\Component\InputProcessor\Processor\IntValue;
use MWStake\MediaWiki\Component\InputProcessor\Processor\KeywordValue;
use MWStake\MediaWiki\Component\InputProcessor\Processor\NamespaceListValue;

class PagesVisited extends GenericTag {

	public function __construct(
		private readonly NamespaceInfo $namespaceInfo,
		private readonly Language $language
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function getTagNames(): array {
		return [ 'pagesvisited', 'bs:pagesvisited' ];
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
		return new PagesVisitedHandler( $services->getTitleFactory(),
		$services->getLinkRenderer() );
	}

	/**
	 * @inheritDoc
	 */
	public function getParamDefinition(): ?array {
		$namespaces = ( new NamespaceListValue( $this->namespaceInfo, $this->language ) )
			->setRequired( false )
			->setListSeparator( ',' )
			->setDefaultValue( [] );
		$count = ( new IntValue() )->setDefaultValue( 7 );
		$maxTitleLength = ( new IntValue() )->setDefaultValue( 20 );
		$order = ( new KeywordValue() )->setKeywords( [ 'time', 'pagename' ] );

		return [
			'namespaces' => $namespaces,
			'maxtitlelength' => $maxTitleLength,
			'count' => $count,
			'order' => $order
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
				'label' => Message::newFromKey( 'bs-pagesvisited-ve-pagesvisited-attr-number-label' )->text(),
				'help' => Message::newFromKey( 'bs-pagesvisited-ve-pagesvisited-attr-count-help' )->text(),
				'value' => 7,
			],
			[
				'type' => 'number',
				'name' => 'maxtitlelength',
				'label' => Message::newFromKey( 'bs-pagesvisited-ve-pagesvisited-attr-maxlength-label' )->text(),
				'help' => Message::newFromKey( 'bs-pagesvisited-ve-pagesvisited-attr-maxtitlelength-help' )->text(),
				'value' => 20,
			],
			[
				'type' => 'dropdown',
				'name' => 'order',
				'label' => Message::newFromKey( 'bs-pagesvisited-ve-pagesvisited-attr-orderby-label' )->text(),
				'help' => Message::newFromKey( 'bs-pagesvisited-ve-pagesvisited-attr-order-help' )->text(),
				'value' => 'time',
				'options' => [
					[
						'data' => 'time',
						'label' => Message::newFromKey(
							'bs-pagesvisited-tag-pagesvisited-attr-order-option-time'
						)->plain()
					],
					[
						'data' => 'pagename',
						'label' => Message::newFromKey(
							'bs-pagesvisited-tag-pagesvisited-attr-order-option-pagename'
						)->plain()
					]
				]
			],
			[
				'type' => 'text',
				'name' => 'namespaces',
				'label' => Message::newFromKey( 'bs-pagesvisited-ve-pagesvisited-attr-filter-namespaces-label' )->text(),
				'help' => Message::newFromKey( 'bs-pagesvisited-ve-pagesvisited-attr-namespaces-help' )->text(),
			]
		] );

		return new ClientTagSpecification(
			'Pagesvisited',
			Message::newFromKey( 'bs-pagesvisited-tag-pagesvisited-desc' ),
			$formSpec,
			Message::newFromKey( 'bs-pagesvisited-ve-pagesvisited-title' )
		);
	}
}
