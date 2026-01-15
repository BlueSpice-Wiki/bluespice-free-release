<?php

namespace MediaWiki\Extension\PDFCreator\PageParams;

use MediaWiki\Extension\PDFCreator\IPageParamsProvider;
use MediaWiki\Extension\PDFCreator\Utility\ParamDesc;
use MediaWiki\Message\Message;
use MediaWiki\Page\PageIdentity;
use MediaWiki\User\UserIdentity;

class PageCount implements IPageParamsProvider {

	/**
	 * @param PageIdentity|null $pageIdentity
	 * @param UserIdentity|null $userIdentity
	 * @return array
	 */
	public function execute( ?PageIdentity $pageIdentity, ?UserIdentity $userIdentity ): array {
		$params = [
			'currentpagenumber' => '<span class="pdfcreator-currentpagenumber"></span>',
			'totalpagescount' => '<span class="pdfcreator-totalpagescount"></span>',
		];
		return $params;
	}

	/**
	 * @param string $language
	 * @return ParamDesc[]
	 */
	public function getParamsDescription( $language ): array {
		return [
			new ParamDesc(
				'currentpagenumber',
				Message::newFromKey( 'pdfcreator-pageparam-currentpagenumber' ),
				1
			),
			new ParamDesc(
				'totalpagescount',
				Message::newFromKey( 'pdfcreator-pageparam-totalpagescount' ),
				5
			)
		];
	}
}
