<?php

namespace MediaWiki\Extension\UserProfile;

use MediaWiki\Config\Config;
use MediaWiki\Extension\UserProfile\Field\EmailField;
use MediaWiki\Extension\UserProfile\Field\LinkField;
use MediaWiki\Extension\UserProfile\Field\MetaField;
use MediaWiki\Extension\UserProfile\Field\ProfileField;
use MediaWiki\Extension\UserProfile\Field\ReadOnlyField;
use MediaWiki\Extension\UserProfile\Field\SystemField;
use MediaWiki\Language\Language;
use MediaWiki\User\Options\UserOptionsLookup;
use MediaWiki\User\User;
use MediaWiki\User\UserIdentity;
use UnexpectedValueException;

class ProfileFieldRegistry {

	/**
	 * @var array
	 */
	private $customFields = [];

	/**
	 * @var UserOptionsLookup
	 */
	private $optionsLookup;

	/**
	 * @param array $attribute
	 * @param Config $config
	 * @param UserOptionsLookup $optionsLookup
	 */
	public function __construct( array $attribute, Config $config, UserOptionsLookup $optionsLookup ) {
		$this->optionsLookup = $optionsLookup;
		$this->initCustomFields( $attribute, $config );
	}

	/**
	 * @param User $subjectUser
	 * @param User $requester
	 * @param Language $language
	 * @return array
	 */
	public function getSerializedFields( User $subjectUser, User $requester, Language $language ): array {
		$data = array_merge(
			$this->getBuiltInFields(),
			$this->customFields
		);

		$serializedFields = [];
		foreach ( $data as $name => $field ) {
			$serializedFields[$name] = $field->serialize( $subjectUser, $requester, $language );
		}
		return $serializedFields;
	}

	/**
	 * @param string $name
	 * @return ProfileField|null
	 */
	public function getField( string $name ): ?ProfileField {
		$data = array_merge(
			$this->getBuiltInFields(),
			$this->customFields
		);
		return $data[$name] ?? null;
	}

	/**
	 * @param Language $language
	 * @return array
	 */
	public function getFieldsForTagDefinition( Language $language ): array {
		$data = array_merge(
			$this->getBuiltInFields(),
			$this->customFields
		);

		$serializedFields = [];
		foreach ( $data as $name => $field ) {
			$serializedFields[$name] =  $field->getLabel( $language )->text();
		}
		return $serializedFields;
	}

	/**
	 * @param UserIdentity $user
	 * @return array
	 */
	public function getPublicFields( UserIdentity $user ): array {
		$data = array_merge(
			$this->getBuiltInFields(),
			$this->customFields
		);
		$public = [];
		foreach ( $data as $name => $field ) {
			if ( $field->isPublic( $user ) ) {
				$public[] = $name;
			}
		}
		return $public;
	}

	/**
	 * @return array
	 */
	public function getAllowedFields(): array {
		$data = array_merge(
			$this->getBuiltInFields(),
			$this->customFields
		);
		$allowed = array_map( static function ( $field ) {
			if ( $field instanceof ReadOnlyField ) {
				return null;
			}
			return $field->getName();
		}, $data );

		return array_filter( $allowed );
	}

	/**
	 * @return array
	 */
	public function getCustomFields(): array {
		return $this->customFields;
	}

	/**
	 * @return array
	 */
	public function getEditableFields(): array {
		$data = array_merge(
			$this->getBuiltInFields(),
			$this->customFields
		);
		$editable = array_map( static function ( $field ) {
			if ( $field instanceof ReadOnlyField ) {
				return null;
			}
			if ( $field instanceof MetaField ) {
				return null;
			}
			return $field->getName();
		}, $data );

		return array_filter( $editable );
	}

	/**
	 * @param string $name
	 * @param ProfileField $field
	 * @return void
	 */
	public function registerField( string $name, ProfileField $field ) {
		$this->customFields[$name] = $field;
	}

	/**
	 * @param string $name
	 * @return void
	 */
	public function unregisterField( string $name ) {
		if ( isset( $this->customFields[$name] ) ) {
			unset( $this->customFields[$name] );
		}
	}

	/**
	 * @param array $attribute
	 * @param Config $config
	 * @return void
	 */
	private function initCustomFields( array $attribute, Config $config ) {
		$attribute = array_merge(
			$attribute,
			$config->get( 'UserProfileFields' )
		);
		foreach ( $attribute as $name => $data ) {
			if ( !isset( $data['msgKey'] ) ) {
				throw new UnexpectedValueException( 'msgKey is required for profile field: ' . $name );
			}
			if ( $data['isMeta'] ?? false ) {
				$this->customFields[$name] = new MetaField(
					$name,
					$data['msgKey'],
					$data['isPublic'] ?? false
				);
			} else {
				if ( isset( $data['url'] ) ) {
					$this->customFields[$name] = new LinkField(
						$data['url'],
						$name,
						$data['msgKey'],
						$data['isPublic'] ?? true,
						$data['formDefinition'] ?? null,
						$data['rlModules'] ?? []
					);
				} else {
					$this->customFields[$name] = new ProfileField(
						$name,
						$data['msgKey'],
						$data['isPublic'] ?? true,
						$data['formDefinition'] ?? null,
						$data['rlModules'] ?? []
					);
				}
			}
		}
	}

	/**
	 * @return ProfileField[]
	 */
	public function getBuiltInFields(): array {
		return [
			'username' => new ReadOnlyField( 'username' ),
			'imageUrl' => new ReadOnlyField( 'imageUrl' ),
			'realName' => new SystemField(
				'realName',
				'userprofile-field-realname',
				true,
				[
					'type' => 'text',
				]
			),
			'email' => new EmailField( $this->optionsLookup ),
			'joined' => new MetaField(
				'joined',
				'userprofile-field-joined',
				true
			),
			'numberOfEdits' => new MetaField(
				'numberOfEdits',
				'userprofile-field-numberofedits',
				true
			),
		];
	}

}
