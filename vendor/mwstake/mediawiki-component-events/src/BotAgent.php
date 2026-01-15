<?php

namespace MWStake\MediaWiki\Component\Events;

use MediaWiki\User\UserIdentity;

class BotAgent implements UserIdentity {

	/**
	 * @param bool $wikiId
	 *
	 * @return int
	 */
	public function getId( $wikiId = self::LOCAL ): int {
		return 0;
	}

	/**
	 * @return string
	 */
	public function getName(): string {
		return "Event Bot";
	}

	/**
	 * @return int
	 */
	public function getActorId() {
		return 0;
	}

	/**
	 * @param UserIdentity|null $user
	 *
	 * @return false
	 */
	public function equals( ?UserIdentity $user ): bool {
		return false;
	}

	/**
	 * @return bool
	 */
	public function isRegistered(): bool {
		return true;
	}

	/**
	 * @param string $wikiId
	 *
	 * @return bool
	 */
	public function assertWiki( $wikiId ) {
		return true;
	}

	/**
	 * @return bool|string
	 */
	public function getWikiId() {
		return self::LOCAL;
	}
}
