<?php

namespace MediaWiki\Extension\UserProfile\Field;

use MediaWiki\Language\Language;
use MediaWiki\User\User;

class ReadOnlyField extends SystemField {

	/**
	 * @param string $name
	 * @param bool $isPublic
	 */
	public function __construct( string $name, bool $isPublic = true ) {
		parent::__construct( $name, '', $isPublic );
	}

	/**
	 * @inheritDoc
	 */
	public function serialize( User $subjectUser, User $requester, Language $language ) {
		return parent::serialize( $subjectUser, $requester, $language ) + [
			'isReadOnly' => true
		];
	}
}
