<?php

namespace MWStake\MediaWiki\Component\AlertBanners\Hook;

interface MWStakeAlertBannersRegisterProvidersHook {

	/**
	 * Allows to add `ObjectFactory` compatible specs. A unique key must be provided.
	 *
	 * @param array &$providerSpecs
	 * @return void
	 */
	public function onMWStakeAlertBannersRegisterProviders( &$providerSpecs ): void;
}
