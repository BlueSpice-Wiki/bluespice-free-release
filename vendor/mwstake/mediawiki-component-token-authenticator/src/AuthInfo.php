<?php

namespace MWStake\MediaWiki\Component\TokenAuthenticator;

use JsonSerializable;
use MediaWiki\User\UserIdentity;

class AuthInfo implements JsonSerializable {

	/**
	 * @param UserIdentity $user
	 * @param string $wikiId
	 * @param string $langCode
	 * @param array $groups
	 * @param array $metadata
	 */
	public function __construct(
		private readonly UserIdentity $user,
		private readonly string $wikiId,
		private readonly string $langCode,
		private readonly array $groups,
		private readonly array $metadata = []
	) {
	}

	/**
	 * @return UserIdentity
	 */
	public function getUser(): UserIdentity {
		return $this->user;
	}

	/**
	 * @return array
	 */
	public function getGroups(): array {
		return $this->groups;
	}

	/**
	 * @return array
	 */
	public function jsonSerialize(): array {
		return [
			'username' => $this->user->getName(),
			'real_name' => $this->user->getRealName(),
			'id' => $this->user->getId(),
			'wiki_id' => $this->wikiId,
			'lang_code' => $this->langCode,
			'groups' => $this->groups,
			'meta' => $this->metadata,
		];
	}
}
