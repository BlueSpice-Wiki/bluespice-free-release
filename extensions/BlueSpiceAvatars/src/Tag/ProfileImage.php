<?php

namespace BlueSpice\Avatars\Tag;

use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\GenericTagHandler\ClientTagSpecification;
use MWStake\MediaWiki\Component\GenericTagHandler\GenericTag;
use MWStake\MediaWiki\Component\GenericTagHandler\ITagHandler;
use MWStake\MediaWiki\Component\InputProcessor\Processor\BooleanValue;
use MWStake\MediaWiki\Component\InputProcessor\Processor\IntValue;
use MWStake\MediaWiki\Component\InputProcessor\Processor\StringValue;

class ProfileImage extends GenericTag {

	/**
	 * @inheritDoc
	 */
	public function getTagNames(): array {
		return [ 'bs:profileimage', 'profileimage' ];
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
	public function getContainerElementName(): ?string {
		return 'div';
	}

	/**
	 * @inheritDoc
	 */
	public function getHandler( MediaWikiServices $services ): ITagHandler {
		return new ProfileImageHandler(
			$services->getService( 'MWStake.DynamicFileDispatcher.Factory' ),
			$services->getService( 'BSRendererFactory' ),
			$services->getUserFactory()
		);
	}

	/**
	 * @inheritDoc
	 */
	public function getParamDefinition(): ?array {
		$username = ( new StringValue() )->setDefaultValue( '' );
		$width = ( new IntValue() )->setDefaultValue( 32 )->setMin( 1 );
		$height = ( new IntValue() )->setDefaultValue( 32 )->setMin( 1 );
		$raw = ( new BooleanValue() )->setDefaultValue( false );

		return [
			'username' => $username,
			'width' => $width,
			'height' => $height,
			'raw' => $raw,
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getClientTagSpecification(): ClientTagSpecification|null {
		return null;
	}
}
