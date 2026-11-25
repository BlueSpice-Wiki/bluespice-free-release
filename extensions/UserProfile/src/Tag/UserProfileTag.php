<?php

namespace MediaWiki\Extension\UserProfile\Tag;

use MediaWiki\Extension\UserProfile\ProfileFieldRegistry;
use MediaWiki\Languages\LanguageFactory;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\User\UserFactory;
use MWStake\MediaWiki\Component\FormEngine\StandaloneFormSpecification;
use MWStake\MediaWiki\Component\GenericTagHandler\ClientTagSpecification;
use MWStake\MediaWiki\Component\GenericTagHandler\GenericTag;
use MWStake\MediaWiki\Component\GenericTagHandler\ITagHandler;
use MWStake\MediaWiki\Component\InputProcessor\Processor\BooleanValue;
use MWStake\MediaWiki\Component\InputProcessor\Processor\KeywordValue;
use MWStake\MediaWiki\Component\InputProcessor\Processor\StringListValue;
use MWStake\MediaWiki\Component\InputProcessor\Processor\UserValue;

class UserProfileTag extends GenericTag {

	/**
	 * @param UserFactory $userFactory
	 * @param ProfileFieldRegistry $profileFieldRegistry
	 * @param LanguageFactory $languageFactory
	 */
	public function __construct(
		private readonly UserFactory $userFactory,
		private readonly ProfileFieldRegistry $profileFieldRegistry,
		private readonly LanguageFactory $languageFactory
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function getTagNames(): array {
		return [ 'user-profile' ];
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
	public function getParamDefinition(): ?array {
		$userValidator = new UserValue( $this->userFactory );
		$userValidator->setRequired( true );

		$framedValidator = new BooleanValue();
		$framedValidator->setRequired( false );
		$framedValidator->setDefaultValue( true );

		$orientationValidator = new KeywordValue();
		$orientationValidator->setRequired( false );
		$orientationValidator->setDefaultValue( 'horizontal' );
		$orientationValidator->setKeywords( [ 'horizontal', 'vertical' ] );

		$fieldsValidator = new StringListValue();
		$fieldsValidator->setRequired( false );
		$fieldsValidator->setListSeparator( ',' );

		return [
			'user' => $userValidator,
			'framed' => $framedValidator,
			'orientation' => $orientationValidator,
			'fields' => $fieldsValidator
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getHandler( MediaWikiServices $services ): ITagHandler {
		return new UserProfileTagHandler( $services->get( 'UserProfile.Manager' ) );
	}

	/**
	 * @inheritDoc
	 */
	public function getClientTagSpecification(): ClientTagSpecification|null {
		$availableFields = [];
		foreach ( $this->getAvailableFieldsForTag() as $key => $field ) {
			$availableFields[] = [
				'data' => $key,
				'label' => $field['label'] ?? $key,
			];
		}

		$formSpec = new StandaloneFormSpecification();
		$formSpec->setItems( [
			[
				'type' => 'user_picker',
				'name' => 'user',
				'label' => Message::newFromKey( 'userprofile-ve-attr-user-label' )->text(),
				'help' => Message::newFromKey( 'userprofile-ve-attr-user-help' )->text(),
				'widget_$overlay' => true,
			],
			[
				'type' => 'checkbox',
				'name' => 'framed',
				'label' => Message::newFromKey( 'userprofile-ve-attr-framed-label' )->text(),
				'labelAlign' => 'inline'
			],
			[
				'type' => 'dropdown',
				'name' => 'orientation',
				'label' => Message::newFromKey( 'userprofile-ve-attr-orientation-label' )->text(),
				'options' => [
					[
						'data' => 'horizontal',
						'label' => Message::newFromKey( 'userprofile-ve-attr-orientation-horizontal' )->text(),
					], [
						'data' => 'vertical',
						'label' => Message::newFromKey( 'userprofile-ve-attr-orientation-vertical' )->text(),
					],
				],
			],
			[
				'type' => 'menutag_multiselect',
				'name' => 'fields',
				'label' => Message::newFromKey( 'userprofile-ve-attr-fields' )->text(),
				'help' => Message::newFromKey( 'userprofile-ve-attr-fields-label-help' )->text(),
				'options' => $availableFields,
				'widget_allowArbitrary' => false,
				'widget_$overlay' => true,
			]
		] );

		return new ClientTagSpecification(
			'UserProfile',
			Message::newFromKey( 'userprofile-droplet-name-description' ),
			$formSpec,
			Message::newFromKey( 'userprofile-droplet-name' )
		);
	}

	/**
	 * @return array
	 */
	private function getAvailableFieldsForTag(): array {
		$user = $this->userFactory->newAnonymous();
		$language = $this->languageFactory->getLanguage( 'en' );
		$allFields = $this->profileFieldRegistry->getSerializedFields(
			$user, $user, $language
		);
		$mandatory = [ 'username', 'realName', 'userDisplay', 'imageUrl' ];

		// Filter out mandatory fields
		return array_filter( $allFields, static function ( $field ) use ( $mandatory ) {
			return !in_array( $field['name'], $mandatory );
		} );
	}
}
