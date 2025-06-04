<?php

namespace MediaWiki\Extension\UserProfile;

use BlueSpice\SMWConnector\PropertyValueProvider;
use MediaWiki\Extension\UserProfile\Field\ProfileField;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use MediaWiki\User\UserIdentity;
use SMWDataItem;
use SMWDIBlob;

class UserProfilePropertyValueProvider extends PropertyValueProvider {

	/**
	 *
	 * @return \BlueSpice\SMWConnector\IPropertyValueProvider[]
	 */
	public static function factory() {
		/** @var ProfileFieldRegistry $fieldRegistry */
		$fieldRegistry = MediaWikiServices::getInstance()->getService(
			'UserProfile.FieldRegistry'
		);
		$manager = MediaWikiServices::getInstance()->getService(
			'UserProfile.Manager'
		);
		$userFactory = MediaWikiServices::getInstance()->getUserFactory();
		$propertyValueProviders = [];
		$public = $fieldRegistry->getPublicFields(
			$userFactory->newAnonymous()
		);
		$blacklist = [ 'username', 'imageUrl' ];
		foreach ( $public as $field ) {
			if ( in_array( $field, $blacklist ) ) {
				continue;
			}
			$smwName = preg_replace( '/\PL/u', '', strtoupper( $field ) );
			if ( empty( $smwName ) ) {
				continue;
			}
			$propertyValueProviders[] = new self( $field, $smwName, $fieldRegistry->getField( $field ), $manager );
		}

		return $propertyValueProviders;
	}

	/**
	 *
	 * @var string
	 */
	private $name;

	/**
	 *
	 * @var string
	 */
	private $smwName;

	/**
	 *
	 * @var ProfileField
	 */
	private $field;

	/**
	 *
	 * @var ProfileManager
	 */
	private $profileManager;

	/**
	 * @var UserIdentity|null
	 */
	private $user = null;

	/**
	 *
	 * @param string $name
	 * @param string $smwName
	 * @param ProfileField $field
	 * @param ProfileManager $profileManager
	 */
	public function __construct( $name, $smwName, ProfileField $field, ProfileManager $profileManager ) {
		$this->name = $name;
		$this->smwName = $smwName;
		$this->field = $field;
		$this->profileManager = $profileManager;
	}

	/**
	 *
	 * @param \SESP\AppFactory $appFactory
	 * @param \SMW\DIProperty $property
	 * @param \SMW\SemanticData $semanticData
	 * @return \SMWDataItem
	 */
	public function addAnnotation( $appFactory, $property, $semanticData ) {
		$maybeUserPage = $semanticData->getSubject()->getTitle();
		if ( !$this->initUser( $maybeUserPage ) ) {
			return null;
		}
		if ( !$this->field->isPublic( $this->user ) ) {
			return null;
		}

		$data = $this->profileManager->getProfileData( $this->user, new User() );

		if ( !isset( $data[$this->name] ) ) {
			return null;
		}
		return new SMWDIBlob( $data[$this->name] );
	}

	/**
	 *
	 * @param Title|null $title
	 * @return bool
	 */
	private function initUser( $title ) {
		if ( !$title || $title->getNamespace() !== NS_USER || $title->isSubpage() ) {
			return false;
		}

		$this->user = MediaWikiServices::getInstance()->getUserFactory()
			->newFromName( $title->getText() );
		if ( !$this->user || !$this->user->isRegistered() ) {
			return false;
		}

		return true;
	}

	/**
	 *
	 * @return int
	 */
	public function getType() {
		return SMWDataItem::TYPE_BLOB;
	}

	/**
	 *
	 * @return string
	 */
	public function getAliasMessageKey() {
		return $this->field->getLabelKey();
	}

	/**
	 *
	 * @return string
	 */
	public function getDescriptionMessageKey() {
		return "{$this->getAliasMessageKey()}-desc";
	}

	/**
	 *
	 * @return string
	 */
	public function getId() {
		return "_PROFILEINFO{$this->smwName}";
	}

	/**
	 * @return string
	 */
	public function getLabel() {
		return "Profile/$this->name";
	}
}
