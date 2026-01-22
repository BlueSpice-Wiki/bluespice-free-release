<?php

namespace BlueSpice\WhoIsOnline\Tag;

use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\FormEngine\StandaloneFormSpecification;
use MWStake\MediaWiki\Component\GenericTagHandler\ClientTagSpecification;
use MWStake\MediaWiki\Component\GenericTagHandler\GenericTag;
use MWStake\MediaWiki\Component\GenericTagHandler\ITagHandler;
use MWStake\MediaWiki\Component\InputProcessor\Processor\StringValue;

class Popup extends GenericTag {

	/** @var int */
	private int $counter = 0;

	/**
	 * @inheritDoc
	 */
	public function getTagNames(): array {
		return [
			'userslink',
			'bs:whoisonline:popup',
			'bs:whoisonlinepopup',
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
	public function getHandler( MediaWikiServices $services ): ITagHandler {
		$id = $this->counter++;
		return new PopupHandler( $services->getService( 'BSWhoIsOnlineTracer' ), $id );
	}

	/**
	 * @inheritDoc
	 */
	public function getParamDefinition(): ?array {
		$anchorText = new StringValue();

		return [
			'anchortext' => $anchorText,
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getClientTagSpecification(): ClientTagSpecification|null {
		$formSpec = new StandaloneFormSpecification();
		$formSpec->setItems( [
			[
				'type' => 'text',
				'name' => 'anchortext',
				'label' => Message::newFromKey( 'bs-whoisonline-tag-whoisonlinepopup-param-text-label' )->text(),
				'help' => Message::newFromKey( 'bs-whoisonline-tag-whoisonlinepopup-param-text-help' )->text(),
				'value' => Message::newFromKey( 'bs-whoisonline-widget-title' )->text()
			]
		] );
		return new ClientTagSpecification(
			'Whoisonlinepopup',
			Message::newFromKey( 'bs-whoisonline-tag-whoisonlinepopup-description' ),
			$formSpec,
			Message::newFromKey( 'bs-whoisonline-ve-whoisonlinepopupinspector-title' )
		);
	}

	/**
	 * @inheritDoc
	 */
	public function getContainerElementName(): ?string {
		return 'span';
	}
}
