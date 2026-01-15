<?php

namespace MediaWiki\Extension\UserProfile\ContentDroplets;

use MediaWiki\Extension\ContentDroplets\Droplet\TagDroplet;
use MediaWiki\Message\Message;

class ProfileDroplet extends TagDroplet {

	/**
	 * @inheritDoc
	 */
	public function getName(): Message {
		return Message::newFromKey( 'userprofile-droplet-name' );
	}

	/**
	 * @inheritDoc
	 */
	public function getDescription(): Message {
		return Message::newFromKey( 'userprofile-droplet-name-description' );
	}

	/**
	 * @inheritDoc
	 */
	public function getIcon(): string {
		return 'droplet-profile';
	}

	/**
	 * @inheritDoc
	 */
	public function getRLModules(): array {
		return [ 'ext.userProfile.visualEditorTagDefinition', 'ext.userProfile.styles' ];
	}

	/**
	 * @return array
	 */
	public function getCategories(): array {
		return [ 'data' ];
	}

	/**
	 *
	 * @return string
	 */
	protected function getTagName(): string {
		return 'user-profile';
	}

	/**
	 * @return array
	 */
	protected function getAttributes(): array {
		return [
			'user' => 'user'
		];
	}

	/**
	 * @return bool
	 */
	protected function hasContent(): bool {
		return false;
	}

	/**
	 * @return string|null
	 */
	public function getVeCommand(): ?string {
		return 'userprofileCommand';
	}

}
