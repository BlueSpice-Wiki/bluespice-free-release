<?php

namespace MediaWiki\Extension\PDFCreator;

use MediaWiki\Extension\PDFCreator\Utility\ParamDesc;
use MediaWiki\Page\PageIdentity;
use MediaWiki\User\UserIdentity;

interface IPageParamsProvider {

	/**
	 * @param PageIdentity|null $pageIdentity
	 * @param UserIdentity|null $userIdentity
	 * @return array
	 */
	public function execute( ?PageIdentity $pageIdentity, ?UserIdentity $userIdentity ): array;

	/**
	 * @param string $languageCode
	 * @return ParamDesc[]
	 */
	public function getParamsDescription( $languageCode ): array;
}
