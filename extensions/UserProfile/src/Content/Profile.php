<?php

namespace MediaWiki\Extension\UserProfile\Content;

use MediaWiki\Content\JsonContent;

class Profile extends JsonContent {

	/**
	 * @param string $text
	 */
	public function __construct( $text ) {
		parent::__construct( $text, CONTENT_MODEL_USER_PROFILE );
	}

	/**
	 * @return array
	 */
	public function getProfileData(): array {
		if ( !$this->isValid() ) {
			return [];
		}
		return json_decode( $this->getText(), true );
	}
}
