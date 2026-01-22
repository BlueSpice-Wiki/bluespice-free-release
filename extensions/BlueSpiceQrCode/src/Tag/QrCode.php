<?php

namespace BlueSpice\QrCode\Tag;

use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Title\TitleFactory;
use MWStake\MediaWiki\Component\FormEngine\StandaloneFormSpecification;
use MWStake\MediaWiki\Component\GenericTagHandler\ClientTagSpecification;
use MWStake\MediaWiki\Component\GenericTagHandler\GenericTag;
use MWStake\MediaWiki\Component\GenericTagHandler\ITagHandler;
use MWStake\MediaWiki\Component\InputProcessor\Processor\IntValue;
use MWStake\MediaWiki\Component\InputProcessor\Processor\StringValue;
use MWStake\MediaWiki\Component\InputProcessor\Processor\TitleValue;

class QrCode extends GenericTag {

	public function __construct(
		private readonly TitleFactory $titleFactory,
		private readonly PermissionManager $permissionManager
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function getTagNames(): array {
		return [ 'qrcode' ];
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
		return new QrCodeHandler( $services->getService( 'MWStake.DynamicFileDispatcher.Factory' ) );
	}

	/**
	 * @inheritDoc
	 */
	public function getParamDefinition(): ?array {
		$page = ( new TitleValue( $this->titleFactory, $this->permissionManager ) )->setRequired( true );
		$query = new StringValue();
		$desc = new StringValue();
		$size = ( new IntValue() )->setDefaultValue( 120 )->setMin( 1 );

		return [
			'page' => $page,
			'query' => $query,
			'desc' => $desc,
			'size' => $size
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getClientTagSpecification(): ClientTagSpecification|null {
		$formSpec = new StandaloneFormSpecification();
		$formSpec->setItems( [
			[
				'type' => 'title',
				'name' => 'page',
				'label' => Message::newFromKey( 'bs-qr-code-tag-attr-page-label' )->text(),
				'help' => Message::newFromKey( 'bs-qr-code-tag-attr-page-help' )->text(),
			],
			[
				'type' => 'text',
				'name' => 'query',
				'label' => Message::newFromKey( 'bs-qr-code-tag-attr-query-label' )->text(),
				'help' => Message::newFromKey( 'bs-qr-code-tag-attr-query-help' )->text(),
			],
			[
				'type' => 'text',
				'name' => 'desc',
				'label' => Message::newFromKey( 'bs-qr-code-tag-attr-desc-label' )->text(),
				'help' => Message::newFromKey( 'bs-qr-code-tag-attr-desc-desc' )->text(),
			],
			[
				'type' => 'dropdown',
				'name' => 'size',
				'label' => Message::newFromKey( 'bs-qr-code-tag-attr-size-label' )->text(),
				'help' => Message::newFromKey( 'bs-qr-code-tag-attr-size-help' )->text(),
				'options' => [
					[
						'data' => 80,
						'label' => Message::newFromKey( 'bs-qr-code-tag-attr-size-option-small' )->text()
					],
					[
						'data' => 120,
						'label' => Message::newFromKey( 'bs-qr-code-tag-attr-size-option-medium' )->text()
					],
					[
						'data' => 400,
						'label' => Message::newFromKey( 'bs-qr-code-tag-attr-size-option-large' )->text()
					]
				],
				'widget_$overlay' => true,
			]
		] );

		return new ClientTagSpecification(
			'QrCode',
			Message::newFromKey( 'bs-qr-code-tag-qrcode-desc' ),
			$formSpec,
			Message::newFromKey( 'bs-qr-code-tag-qrcode-title' )
		);
	}
}
