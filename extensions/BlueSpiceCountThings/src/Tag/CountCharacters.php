<?php

namespace BlueSpice\CountThings\Tag;

use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\FormEngine\StandaloneFormSpecification;
use MWStake\MediaWiki\Component\GenericTagHandler\ClientTagSpecification;
use MWStake\MediaWiki\Component\GenericTagHandler\GenericTag;
use MWStake\MediaWiki\Component\GenericTagHandler\ITagHandler;
use MWStake\MediaWiki\Component\InputProcessor\Processor\KeywordValue;

class CountCharacters extends GenericTag {

	/**
	 * @inheritDoc
	 */
	public function getTagNames(): array {
		return [ 'bs:countcharacters', 'countcharacters' ];
	}

	/**
	 * @inheritDoc
	 */
	public function hasContent(): bool {
		return true;
	}

	public function shouldParseInput(): bool {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function getHandler( MediaWikiServices $services ): ITagHandler {
		return new CountCharactersHandler(
			$services->getLinkRenderer(),
			$services->getService( 'BSRendererFactory' ),
			$services->getTitleFactory()
		);
	}

	/**
	 * @inheritDoc
	 */
	public function getParamDefinition(): ?array {
		$mode = ( new KeywordValue() )
			->setRequired( true )
			->setKeywords(
				[ 'all', 'chars', 'words', 'chars words', 'pages' ]
			)
			->setDefaultValue( 'all' );

		return [ 'mode' => $mode ];
	}

	/**
	 * @inheritDoc
	 */
	public function getClientTagSpecification(): ClientTagSpecification|null {
		$formSpec = new StandaloneFormSpecification();

		$formSpec->setItems( [
			[
				'type' => 'dropdown',
				'name' => 'mode',
				'label' => Message::newFromKey( 'bs-countthings-ve-countthingsinspector-mode' )->text(),
				'help' => Message::newFromKey( 'bs-countthings-tag-countcharacters-desc-mode-help' )->text(),
				'widget_$overlay' => true,
				'options' => [
					[
						'data' => 'all',
						'label' => Message::newFromKey( 'bs-countthings-ve-countcharacters-mode-all' )->text()
					],
					[
						'data' => 'chars',
						'label' => Message::newFromKey( 'bs-countthings-ve-countcharacters-mode-charsonly' )->text()
					],
					[
						'data' => 'words',
						'label' => Message::newFromKey( 'bs-countthings-ve-countcharacters-mode-wordsonly' )->text()
					],
					[
						'data' => 'chars words',
						'label' => Message::newFromKey(
							'bs-countthings-ve-countcharacters-mode-wordsandchars'
						)->text()
					],
					[
						'data' => 'pages',
						'label' => Message::newFromKey( 'bs-countthings-ve-countcharacters-mode-pagesonly' )->text()
					]
				]
			],
		] );

		return new ClientTagSpecification(
			'CountCharacters',
			Message::newFromKey( 'bs-countthings-tag-countcharacters-desc' ),
			$formSpec,
			Message::newFromKey( 'bs-countthings-ve-countcharacters-title' ),
			null,
			false
		);
	}
}
