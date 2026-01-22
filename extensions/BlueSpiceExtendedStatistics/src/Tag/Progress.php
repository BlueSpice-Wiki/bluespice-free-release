<?php

namespace BlueSpice\ExtendedStatistics\Tag;

use BsPageContentProvider;
use MediaWiki\Context\RequestContext;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\FormEngine\StandaloneFormSpecification;
use MWStake\MediaWiki\Component\GenericTagHandler\ClientTagSpecification;
use MWStake\MediaWiki\Component\GenericTagHandler\GenericTag;
use MWStake\MediaWiki\Component\GenericTagHandler\ITagHandler;
use MWStake\MediaWiki\Component\InputProcessor\Processor\IntValue;
use MWStake\MediaWiki\Component\InputProcessor\Processor\StringValue;

class Progress extends GenericTag {

	/**
	 * @inheritDoc
	 */
	public function getTagNames(): array {
		return [ 'bs:statistics:progress' ];
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
	public function getHandler( MediaWikiServices $services ): ITagHandler {
		return new ProgressHandler(
			BsPageContentProvider::getInstance(),
			RequestContext::getMain()->getTitle()
		);
	}

	/**
	 * @inheritDoc
	 */
	public function getParamDefinition(): ?array {
		$count = ( new IntValue() )->setDefaultValue( 100 );
		$baseItem = ( new StringValue() )->setDefaultValue( '' );
		$progressItem = ( new StringValue() )->setDefaultValue( 'OK' );
		$width = ( new IntValue() )->setDefaultValue( 100 );

		return [
			'basecount' => $count,
			'baseitem' => $baseItem,
			'progressitem' => $progressItem,
			'width' => $width
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
				'name' => 'basecount',
				'label' => Message::newFromKey( 'bs-statistics-ve-progress-attr-basecount-label' )->text(),
				'help' => Message::newFromKey( 'bs-statistics-ve-progress-attr-basecount-help' )->text(),
				'value' => 100
			],
			[
				'type' => 'text',
				'name' => 'baseitem',
				'label' => Message::newFromKey( 'bs-statistics-ve-progress-attr-baseitem-label' )->text(),
				'help' => Message::newFromKey( 'bs-statistics-ve-progress-attr-baseitem-help' )->text(),
			],
			[
				'type' => 'text',
				'name' => 'progressitem',
				'label' => Message::newFromKey( 'bs-statistics-ve-progress-attr-progressitem-label' )->text(),
				'help' => Message::newFromKey( 'bs-statistics-ve-progress-attr-progressitem-help' )->text(),
				'value' => 'OK'
			],
			[
				'type' => 'number',
				'name' => 'width',
				'label' => Message::newFromKey( 'bs-statistics-ve-progress-attr-width-label' )->text(),
				'help' => Message::newFromKey( 'bs-statistics-ve-progress-attr-width-help' )->text(),
				'value' => 150
			]
		] );

		return new ClientTagSpecification(
			'Progress',
			Message::newFromKey( 'bs-statistics-tag-progress-desc' ),
			$formSpec,
			Message::newFromKey( 'bs-statistics-ve-progressinspector-title' )
		);
	}
}
