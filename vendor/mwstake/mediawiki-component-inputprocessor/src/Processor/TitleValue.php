<?php

namespace MWStake\MediaWiki\Component\InputProcessor\Processor;

use MediaWiki\Permissions\PermissionManager;
use MWStake\MediaWiki\Component\InputProcessor\GenericProcessor;
use StatusValue;
use Title;
use TitleFactory;
use User;

class TitleValue extends GenericProcessor {

	/** @var TitleFactory */
	protected TitleFactory $titleFactory;

	/** @var PermissionManager */
	protected PermissionManager $permissionManager;

	/** @var bool */
	protected bool $mustExist = false;

	/** @var User|null */
	protected ?User $userMustBeAbleToRead = null;

	/** @var array|null */
	protected ?array $allowedNamespaces = null;

	/** @var array|null */
	protected ?array $blacklistedNamespaces = null;

	/**
	 * @param TitleFactory $titleFactory
	 * @param PermissionManager $permissionManager
	 */
	public function __construct( TitleFactory $titleFactory, PermissionManager $permissionManager ) {
		$this->titleFactory = $titleFactory;
		$this->permissionManager = $permissionManager;
	}

	/**
	 * @inheritDoc
	 */
	public function initializeFromSpec( array $spec ): static {
		parent::initializeFromSpec( $spec );
		$this->setMustExist( $spec['mustExist'] ?? false );
		$this->setUserMustBeAbleToRead( $spec['userMustBeAbleToRead'] ?? null );
		$this->setAllowedNamespaces( $spec['allowedNamespaces'] ?? null );
		$this->setBlacklistedNamespace( $spec['blacklistedNamespaces'] ?? null );
		return $this;
	}

	/**
	 * @param bool $mustExist
	 * @return $this
	 */
	public function setMustExist( bool $mustExist ): static {
		$this->mustExist = $mustExist;
		return $this;
	}

	/**
	 * @param User|null $user
	 * @return $this
	 */
	public function setUserMustBeAbleToRead( ?User $user ): static {
		$this->userMustBeAbleToRead = $user;
		return $this;
	}

	/**
	 * @param array|null $namespaces
	 * @return $this
	 */
	public function setAllowedNamespaces( ?array $namespaces ): static {
		$this->allowedNamespaces = $namespaces;
		return $this;
	}

	/**
	 * @param array|null $namespaces
	 * @return $this
	 */
	public function setBlacklistedNamespace( ?array $namespaces ): static {
		$this->blacklistedNamespaces = $namespaces;
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function process( mixed $value, string $fieldKey ): StatusValue {
		$parentStatus = parent::process( $value, $fieldKey );
		if ( !$parentStatus->isGood() ) {
			return $parentStatus;
		}
		if ( !$this->isRequired() && ( $value === null ) ) {
			return StatusValue::newGood( $this->getDefaultValue() );
		}
		$title = $this->getTitle( $value );
		if ( !$title ) {
			return StatusValue::newFatal( 'inputprocessor-error-title-invalid', $fieldKey, $value );
		}
		if ( $this->mustExist && !$this->titleExists( $title ) ) {
			return StatusValue::newFatal( 'inputprocessor-error-title-not-exists', $fieldKey, $value );
		}
		if ( $this->allowedNamespaces !== null && !in_array( $title->getNamespace(), $this->allowedNamespaces ) ) {
			return StatusValue::newFatal( 'inputprocessor-error-title-not-allowed-namespace', $fieldKey, $value );
		}
		if ( $this->blacklistedNamespaces && in_array( $title->getNamespace(), $this->blacklistedNamespaces ) ) {
			return StatusValue::newFatal( 'inputprocessor-error-title-not-allowed-namespace', $fieldKey, $value );
		}
		if (
			$this->userMustBeAbleToRead &&
			!$this->permissionManager->userCan( 'read', $this->userMustBeAbleToRead, $title )
		) {
			return StatusValue::newFatal( 'inputprocessor-error-title-no-read-permission', $fieldKey, $value );
		}

		return StatusValue::newGood( $title );
	}

	/**
	 * @param mixed $value
	 * @return Title|null
	 */
	protected function getTitle( mixed $value ): ?Title {
		return $this->titleFactory->newFromText( (string)$value );
	}

	/**
	 * @param Title $title
	 * @return bool
	 */
	protected function titleExists( Title $title ): bool {
		return $title->exists();
	}

	/**
	 * @return mixed
	 */
	public function jsonSerialize(): mixed {
		return array_merge( parent::jsonSerialize(), [
			'type' => 'title',
			'mustExist' => $this->mustExist,
			'userMustBeAbleToRead' => $this->userMustBeAbleToRead?->getName(),
			'allowedNamespaces' => $this->allowedNamespaces,
			'blacklistedNamespaces' => $this->blacklistedNamespaces,
		] );
	}
}
