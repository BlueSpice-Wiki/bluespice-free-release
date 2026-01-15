<?php

namespace PageHeader\PageInfoSentence;

use MediaWiki\Config\Config;
use MediaWiki\Context\IContextSource;

abstract class Type implements IType {

	/**
	 * @var IContextSource
	 */
	protected $context = null;

	/**
	 *
	 * @var Config
	 */
	protected $config = null;

	/**
	 *
	 * @param IContextSource $context
	 * @param Config $config
	 */
	public function __construct( IContextSource $context, Config $config ) {
		$this->config = $config;
		$this->context = $context;
	}

	/**
	 *
	 * @param IContextSource $context
	 * @param Config $config
	 * @return IType
	 */
	public static function factory( IContextSource $context, Config $config ) {
		return new static( $context, $config );
	}

	/**
	 * @deprecated since version 1.0 - should be done elsewhere when we switch to
	 * mwstake/mediawiki-component-commonuserinterface
	 * @param array $data
	 * @return array
	 */
	protected function makeHtmlDataArray( $data ) {
		$htmlData = [];

		foreach ( $data as $key => $value ) {
			$htmlData['data-' . $key] = $value;
		}

		return $htmlData;
	}
}
