<?php

namespace MediaWiki\Extension\UserProfile\Field;

use MediaWiki\Language\Language;
use MediaWiki\User\User;

class LinkField extends ProfileField {

	/** @var string */
	private $url;

	/**
	 * @param string $url
	 * @param string $name
	 * @param string $msgKey
	 * @param bool $isPublic
	 * @param array|null $formDefinition
	 * @param array $rlModules
	 */
	public function __construct(
		string $url, string $name, string $msgKey, bool $isPublic, ?array $formDefinition = null, array $rlModules = []
	) {
		parent::__construct( $name, $msgKey, $isPublic, $formDefinition, $rlModules );
		$this->url = $url;
	}

	/**
	 * @return string
	 */
	public function getUrl(): string {
		return $this->url;
	}

	/**
	 * @param User $subjectUser
	 * @param User $requester
	 * @param Language $language
	 * @return array
	 */
	public function serialize( User $subjectUser, User $requester, Language $language ) {
		return parent::serialize( $subjectUser, $requester, $language ) + [
			'url' => $this->url
		];
	}
}
