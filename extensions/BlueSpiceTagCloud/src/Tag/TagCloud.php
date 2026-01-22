<?php

namespace BlueSpice\TagCloud\Tag;

use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\FormEngine\StandaloneFormSpecification;
use MWStake\MediaWiki\Component\GenericTagHandler\ClientTagSpecification;
use MWStake\MediaWiki\Component\GenericTagHandler\GenericTag;
use MWStake\MediaWiki\Component\GenericTagHandler\ITagHandler;
use MWStake\MediaWiki\Component\GenericTagHandler\MarkerType;
use MWStake\MediaWiki\Component\InputProcessor\Processor\BooleanValue;
use MWStake\MediaWiki\Component\InputProcessor\Processor\IntValue;
use MWStake\MediaWiki\Component\InputProcessor\Processor\KeywordValue;
use MWStake\MediaWiki\Component\InputProcessor\Processor\PercentValue;
use MWStake\MediaWiki\Component\InputProcessor\Processor\StringValue;

class TagCloud extends GenericTag {

	/**
	 * @inheritDoc
	 */
	public function getTagNames(): array {
		return [ 'bs:tagcloud' ];
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
		return new TagCloudHandler(
			$services->getService( 'BSTagCloudFactory' ),
			$services->getUserFactory(),
			$services->getConfigFactory()
		);
	}

	/**
	 * @inheritDoc
	 */
	public function getResourceLoaderModules(): ?array {
		return [ 'ext.bluespice.tagcloud.text.styles', 'ext.bluespice.tagcloud.canvas3d' ];
	}

	/**
	 * @inheritDoc
	 */
	public function getParamDefinition(): ?array {
		$renderer = ( new KeywordValue() )
			->setKeywords( [ 'text', 'list', 'canvas3d' ] )
			->setDefaultValue( 'list' );
		$store = ( new KeywordValue() )
			->setKeywords( [ 'category', 'searchstats' ] )
			->setDefaultValue( 'category' );
		$width = ( new PercentValue() )
			->setDefaultValue( 100 );
		$showCount = ( new BooleanValue() )->setDefaultValue( true );
		$minSize = ( new IntValue() )->setDefaultValue( 5 )->setMin( 1 );
		$maxSize = ( new IntValue() )->setDefaultValue( 30 )->setMin( 1 );
		$exclude = new StringValue();

		return [
			'renderer' => $renderer,
			'store' => $store,
			'width' => $width,
			'showcount' => $showCount,
			'minsize' => $minSize,
			'maxsize' => $maxSize,
			'exclude' => $exclude,
			// B/C
			'type' => ( clone $store )->setDefaultValue( '' ),
			'viewtype' => ( clone $renderer )->setDefaultValue( '' ),
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getClientTagSpecification(): ClientTagSpecification|null {
		$formSpec = new StandaloneFormSpecification();
		$formSpec->setItems( [
			[
				'type' => 'layout_index',
				'name' => 'tab_index',
				'tabs' => [
					[
						'type' => 'layout_index_tab',
						'name' => 'common',
						'label' => Message::newFromKey( 'bs-tagcloud-tag-tagcloud-tab-common' )->text(),
						'items' => [
							[
								'type' => 'dropdown',
								'name' => 'renderer',
								'label' => Message::newFromKey(
									'bs-tagcloud-tag-tagcloud-attr-renderer-dropdown-label'
								)->text(),
								'help' => Message::newFromKey( 'bs-tagcloud-tag-tagcloud-attr-renderer-help' )->text(),
								'value' => 'list',
								'labelAlign' => 'top',
								'options' => [
									[
										'data' => 'text',
										'label' => Message::newFromKey(
											'bs-tagcloud-tag-tagcloud-attr-renderer-option-text'
										)->text()
									],
									[
										'data' => 'list',
										'label' => Message::newFromKey(
											'bs-tagcloud-tag-tagcloud-attr-renderer-option-list'
										)->text()
									],
									[
										'data' => 'canvas3d',
										'label' => Message::newFromKey(
											'bs-tagcloud-tag-tagcloud-attr-renderer-option-canvas3d'
										)->text()
									]
								],
								'widget_$overlay' => true,
							],
							[
								'type' => 'dropdown',
								'name' => 'store',
								'label' => Message::newFromKey( 'bs-tagcloud-tag-tagcloud-attr-store-label' )->text(),
								'help' => Message::newFromKey( 'bs-tagcloud-tag-tagcloud-attr-store-help' )->text(),
								'value' => 'category',
								'labelAlign' => 'top',
								'options' => [
									[
										'data' => 'category',
										'label' => Message::newFromKey(
											'bs-tagcloud-tag-tagcloud-attr-store-option-category'
										)->text()
									],
									[
										'data' => 'searchstats',
										'label' => Message::newFromKey(
											'bs-tagcloud-tag-tagcloud-attr-store-option-searchstats'
										)->text()
									]
								],
								'widget_$overlay' => true,
							],
							[
								'type' => 'percent',
								'name' => 'width',
								'label' => Message::newFromKey( 'bs-tagcloud-tag-tagcloud-attr-width-label' )->text(),
								'help' => Message::newFromKey( 'bs-tagcloud-tag-tagcloud-attr-width-help' )->text(),
								'widget_min' => 1,
								'widget_max' => 100,
								'value' => 100,
								'labelAlign' => 'top'
							],
							[
								'type' => 'checkbox',
								'name' => 'showcount',
								'labelAlign' => 'inline',
								'label' => Message::newFromKey(
									'bs-tagcloud-tag-tagcloud-attr-showcount-checkbox-label'
								)->text(),
								'help' => Message::newFromKey(
									'bs-tagcloud-tag-tagcloud-attr-showcount-checkbox-help'
								)->text(),
								'value' => true
							],
						]
					],
					[
						'type' => 'layout_index_tab',
						'name' => 'advanced',
						'label' => Message::newFromKey( 'bs-tagcloud-tag-tagcloud-tab-advanced' )->text(),
						'items' => [
							[
								'type' => 'number',
								'name' => 'minsize',
								'label' => Message::newFromKey( 'bs-tagcloud-tag-tagcloud-attr-minsize-label' )->text(),
								'help' => Message::newFromKey( 'bs-tagcloud-tag-tagcloud-attr-minsize-help' )->text(),
								'value' => 5,
								'labelAlign' => 'top'
							],
							[
								'type' => 'number',
								'name' => 'maxsize',
								'label' => Message::newFromKey(
									'bs-tagcloud-tag-tagcloud-attr-maxsize-label'
								)->text(),
								'help' => Message::newFromKey( 'bs-tagcloud-tag-tagcloud-attr-maxsize-help' )->text(),
								'labelAlign' => 'top'
							],
							[
								'type' => 'text',
								'name' => 'exclude',
								'label' => Message::newFromKey(
									'bs-tagcloud-tag-tagcloud-attr-exclude-label'
								)->text(),
								'help' => Message::newFromKey( 'bs-tagcloud-tag-tagcloud-attr-exclude-help' )->text(),
								'labelAlign' => 'top'
							]
						]
					]
				]
			]
		] );

		return new ClientTagSpecification(
			'TagCloud',
			Message::newFromKey( 'bs-tagcloud-tag-tagcloud-desc' ),
			$formSpec,
			Message::newFromKey( 'bs-tagcloud-tag-tagcloud-title' )
		);
	}
}
