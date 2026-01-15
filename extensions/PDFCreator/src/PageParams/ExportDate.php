<?php

namespace MediaWiki\Extension\PDFCreator\PageParams;

use MediaWiki\Extension\PDFCreator\IPageParamsProvider;
use MediaWiki\Extension\PDFCreator\Utility\ParamDesc;
use MediaWiki\Language\Language;
use MediaWiki\Message\Message;
use MediaWiki\Page\PageIdentity;
use MediaWiki\User\UserIdentity;

class ExportDate implements IPageParamsProvider {

	/** @var Language */
	private $language;

	/**
	 * @param Language $language
	 */
	public function __construct( Language $language ) {
		$this->language = $language;
	}

	/**
	 * @param PageIdentity|null $pageIdentity
	 * @param UserIdentity|null $userIdentity
	 * @return array
	 */
	public function execute( ?PageIdentity $pageIdentity, ?UserIdentity $userIdentity ): array {
		$timestamp = wfTimestampNow();
		if ( $timestamp !== null ) {
			$date = $this->language->date( $timestamp, true );
			$time = $this->language->time( $timestamp, true );
			$params['export-date'] = $date;
			$params['export-time'] = $time;
		}
		return $params;
	}

	/**
	 * @param string $languageCode
	 * @return ParamDesc[]
	 */
	public function getParamsDescription( $languageCode ): array {
		return [
			new ParamDesc(
				'export-date',
				Message::newFromKey( 'pdfcreator-pageparam-export-date' )
			),
			new ParamDesc(
				'export-time',
				Message::newFromKey( 'pdfcreator-pageparam-export-time' )
			)
		];
	}
}
