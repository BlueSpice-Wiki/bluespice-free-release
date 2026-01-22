<?php

namespace BlueSpice\CountThings\Tag;

use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\FormEngine\StandaloneFormSpecification;
use MWStake\MediaWiki\Component\GenericTagHandler\ClientTagSpecification;
use MWStake\MediaWiki\Component\GenericTagHandler\GenericTag;
use MWStake\MediaWiki\Component\GenericTagHandler\ITagHandler;
use MWStake\MediaWiki\Component\InputProcessor\Processor\BooleanValue;

class CountFiles extends GenericTag {

	/**
	 * @inheritDoc
	 */
	public function getTagNames(): array {
		return [ 'bs:countfiles', 'countfiles' ];
	}

	/**
	 * @inheritDoc
	 */
	public function getContainerElementName(): ?string {
		return 'span';
	}

	/**
	 * @inheritDoc
	 */
	public function hasContent(): bool {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function getHandler( MediaWikiServices $services ): ITagHandler {
		return new CountFilesHandler( $services->getDBLoadBalancer() );
	}

	/**
	 * @inheritDoc
	 */
	public function getParamDefinition(): ?array {
		$noDuplicated = ( new BooleanValue() )->setDefaultValue( false );

		return [ 'noduplicates' => $noDuplicated ];
	}

	/**
	 * @inheritDoc
	 */
	public function getClientTagSpecification(): ClientTagSpecification|null {
		$formSpec = new StandaloneFormSpecification();
		$formSpec->setItems(
			[
				[
					'type' => 'checkbox',
					'name' => 'noduplicates',
					'label' => Message::newFromKey( 'bs-countthings-tag-countfiles-count-duplicates-label' )->text(),
					'labelAlign' => 'inline'
				]
			]
		);

		return new ClientTagSpecification(
			'CountFiles',
			Message::newFromKey( 'bs-countthings-tag-countfiles-desc' ),
			$formSpec,
			Message::newFromKey( 'bs-countthings-ve-countfiles-title' )
		);
	}
}
