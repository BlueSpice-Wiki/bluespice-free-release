<?php

namespace PageHeader;

use MediaWiki\Config\Config;
use MediaWiki\Context\IContextSource;

abstract class PageInfo implements IPageInfo {

	/**
	 *
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
	protected function __construct( IContextSource $context, Config $config ) {
		$this->config = $config;
		$this->context = $context;
	}

	/**
	 *
	 * @param IContextSource $context
	 * @param Config $config
	 * @return IPageHeader
	 */
	public static function factory( IContextSource $context, Config $config ) {
		return new static( $context, $config );
	}

	/**
	 *
	 * @return string
	 */
	public function getUrl() {
		return '';
	}

	/**
	 *
	 * @return int
	 */
	public function getPosition() {
		return 100;
	}

	/**
	 *
	 * @return string
	 */
	public function getType() {
		return IPageInfo::TYPE_TEXT;
	}

	/**
	 *
	 * @return string
	 */
	public function getHtmlClass() {
		return '';
	}

	/**
	 *
	 * @return string
	 */
	public function getHtmlId() {
		return '';
	}

	/**
	 * @return array with html attributes data-*
	 */
	public function getHtmlDataAttribs() {
		return [];
	}

	/**
	 * @deprecated since version 1.0
	 * @return string
	 */
	public function getMenu() {
		return '';
	}

	/**
	 *
	 * @return array
	 */
	public function getTypeData(): array {
		return [];
	}
}
