<?php

namespace MWStake\MediaWiki\Component\AlertBanners;

interface IAlertProvider {

	public const TYPE_SUCCESS = 'success';
	public const TYPE_INFO = 'info';
	public const TYPE_WARNING = 'warning';
	public const TYPE_DANGER = 'danger';

	/**
	 * @return string
	 */
	public function getHTML();

	/**
	 * @return string One to the IAlertProvider::TYPE_* constants
	 */
	public function getType();
}
