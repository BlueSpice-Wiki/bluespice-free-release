<?php

namespace MediaWiki\Extension\UserProfile\Field;

use MediaWiki\Language\Language;
use MediaWiki\User\User;

class MetaField extends ProfileField {

	/**
	 * @param string $name
	 * @param string $msgKey
	 * @param bool $isPublic
	 */
	public function __construct(
		string $name, string $msgKey, bool $isPublic
	) {
		parent::__construct( $name, $msgKey, $isPublic );
	}

	/**
	 * @inheritDoc
	 */
	public function serialize( User $subjectUser, User $requester, Language $language ) {
		return parent::serialize( $subjectUser, $requester, $language ) + [
			'isMeta' => true
		];
	}
}
