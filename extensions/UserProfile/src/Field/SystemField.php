<?php

namespace MediaWiki\Extension\UserProfile\Field;

use MediaWiki\Language\Language;
use MediaWiki\User\User;

class SystemField extends ProfileField {

	/**
	 * @inheritDoc
	 */
	public function serialize( User $subjectUser, User $requester, Language $language ) {
		return parent::serialize( $subjectUser, $requester, $language ) + [
			'isSystem' => true
		];
	}
}
