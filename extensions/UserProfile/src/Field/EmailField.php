<?php

namespace MediaWiki\Extension\UserProfile\Field;

use MediaWiki\User\Options\UserOptionsLookup;
use MediaWiki\User\User;

class EmailField extends LinkField {

	/**
	 * @var UserOptionsLookup
	 */
	private $userOptionsLookup;

	/**
	 * @param UserOptionsLookup $userOptionsLookup
	 */
	public function __construct( UserOptionsLookup $userOptionsLookup ) {
		parent::__construct( 'mailto:{value}', 'email', 'userprofile-field-email', false, [
			'type' => 'text',
			'widget_type' => 'email'
		] );
		$this->userOptionsLookup = $userOptionsLookup;
	}

	/**
	 * @param User $forUser
	 * @return bool
	 */
	public function isPublic( User $forUser ): bool {
		return (bool)$this->userOptionsLookup->getOption( $forUser, 'user-profile-mail-public' );
	}
}
