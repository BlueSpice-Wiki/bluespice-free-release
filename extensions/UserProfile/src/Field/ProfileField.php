<?php

namespace MediaWiki\Extension\UserProfile\Field;

use MediaWiki\Language\Language;
use MediaWiki\Message\Message;
use MediaWiki\User\User;

class ProfileField {

	/** @var string */
	private $name;

	/** @var string */
	private $msgKey;

	/** @var bool */
	private $public;

	/** @var array|null */
	private $formDefinition;

	/** @var array */
	private $rlModules;

	/**
	 * @param string $name
	 * @param string $msgKey
	 * @param bool $isPublic
	 * @param array|null $formDefinition
	 * @param array $rlModules
	 */
	public function __construct(
		string $name, string $msgKey, bool $isPublic, ?array $formDefinition = null, array $rlModules = []
	) {
		$this->name = $name;
		$this->msgKey = $msgKey;
		$this->public = $isPublic;
		$this->formDefinition = $formDefinition;
		$this->rlModules = $rlModules;
	}

	/**
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * @param Language $language
	 * @return Message
	 */
	public function getLabel( Language $language ): Message {
		return Message::newFromKey( $this->msgKey );
	}

	/**
	 * @return string
	 */
	public function getLabelKey(): string {
		return $this->msgKey;
	}

	/**
	 * @param User $forUser
	 * @return bool
	 */
	public function isPublic( User $forUser ): bool {
		return $this->public;
	}

	/**
	 * @param User $forUser
	 * @param Language $language
	 * @return array|null
	 */
	public function getFormDefinition( User $forUser, Language $language ): ?array {
		if ( !$this->formDefinition ) {
			return null;
		}
		$this->formDefinition['name'] = $this->formDefinition['name'] ?? $this->name;
		$this->formDefinition['label'] = $this->formDefinition['label'] ?? $this->getLabel( $language )->text();
		return $this->formDefinition;
	}

	/**
	 * @param User $subjectUser
	 * @param User $requester
	 * @param Language $language
	 * @return array
	 */
	public function serialize( User $subjectUser, User $requester, Language $language ) {
		return [
			'name' => $this->name,
			'label' => $this->getLabel( $language )->text(),
			'isPublic' => $this->isPublic( $subjectUser ),
			'formDefinition' => $this->getFormDefinition( $requester, $language ),
			'rlModules' => $this->rlModules
		];
	}
}
