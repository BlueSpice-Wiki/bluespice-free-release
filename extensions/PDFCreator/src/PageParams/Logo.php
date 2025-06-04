<?php

namespace MediaWiki\Extension\PDFCreator\PageParams;

use MediaWiki\Config\Config;
use MediaWiki\Extension\PDFCreator\IPageParamsProvider;
use MediaWiki\Extension\PDFCreator\Utility\ParamDesc;
use MediaWiki\Html\Html;
use MediaWiki\Message\Message;
use MediaWiki\Page\PageIdentity;
use MediaWiki\User\UserIdentity;

class Logo implements IPageParamsProvider {

	/** @var Config */
	private $config;

	/**
	 *
	 * @param Config $config
	 */
	public function __construct( Config $config ) {
		$this->config = $config;
	}

	/**
	 * @inheritDoc
	 */
	public function execute( ?PageIdentity $pageIdentity, ?UserIdentity $userIdentity ): array {
		$params = [];
		$logos = $this->config->get( 'Logos' );
		if ( isset( $logos['1x'] ) ) {
			$logoUrl = $logos['1x'];
			$logoPathParts = explode( '/', $logoUrl );
			$count = count( $logoPathParts );
			$logoName = $logoPathParts[ $count - 1 ];
			$html = Html::openElement( 'img', [
				'src' => 'images/' . $logoName
			] );
			$html .= Html::closeElement( 'img' );
			$params['logo'] = $html;
		}

		return $params;
	}

	/**
	 * @param string $language
	 * @return ParamDesc[]
	 */
	public function getParamsDescription( $language ): array {
		return [
			new ParamDesc(
				'logo',
				Message::newFromKey( 'pdfcreator-pageparam-logo' )
			)
		];
	}
}
