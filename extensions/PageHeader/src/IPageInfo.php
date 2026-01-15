<?php

namespace PageHeader;

use MediaWiki\Context\IContextSource;
use MediaWiki\Message\Message;

interface IPageInfo {
	public const TYPE_TEXT = 'text';
	public const TYPE_LINK = 'link';
	public const TYPE_MENU = 'menu';

	public const ITEMCLASS_PRO = 'pro';
	public const ITEMCLASS_CONTRA = 'contra';

	/**
	 *
	 * @return Message
	 */
	public function getLabelMessage();

	/**
	 *
	 * @return Message
	 */
	public function getTooltipMessage();

	/**
	 *
	 * @return string
	 */
	public function getName();

	/**
	 *
	 * @return string
	 */
	public function getUrl();

	/**
	 *
	 * @return int
	 */
	public function getPosition();

	/**
	 * @return string Can be one of IPageInfo::TYPE_*
	 */
	public function getType();

	/**
	 *
	 * @return string
	 */
	public function getHtmlClass();

	/**
	 *
	 * @return string
	 */
	public function getHtmlId();

	/**
	 * @return array with html attributes data-*
	 */
	public function getHtmlDataAttribs();

	/**
	 * @deprecated since version 1.0
	 * @return string
	 */
	public function getMenu();

	/**
	 *
	 * @param IContextSource $context
	 * @return bool
	 */
	public function shouldShow( $context );

	/**
	 * @return string Can be one of IPageInfo::ITEMCLASS_*
	 */
	public function getItemClass();

	/**
	 * @return array
	 */
	public function getTypeData(): array;
}
