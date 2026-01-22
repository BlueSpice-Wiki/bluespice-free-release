<?php

namespace MWStake\MediaWiki\Component\AlertBanners;

use Exception;

interface IAlertProviderFactory {

	/**
	 * @param IAlertProvider[] $providers
	 * @return IAlertProvider[]
	 * @throws Exception E.g. in case a IAlertProvider object could not be constructed
	 */
	public function processProviders( $providers );
}
