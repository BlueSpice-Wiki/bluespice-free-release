<?php

namespace MWStake\MediaWiki\Component\DataStore;

use MediaWiki\Config\Config;
use MediaWiki\Context\IContextSource;
use MediaWiki\Context\RequestContext;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use MediaWiki\User\User;

abstract class Writer implements IWriter {

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
	 * @param IContextSource|null $context
	 * @param Config|null $config
	 */
	public function __construct( ?IContextSource $context = null, ?Config $config = null ) {
		$this->context = $context;
		if ( $this->context === null ) {
			$this->context = RequestContext::getMain();
		}

		$this->config = $config;
		if ( $this->config === null ) {
			$this->config = MediaWikiServices::getInstance()->getMainConfig();
		}
	}

	/**
	 *
	 * @return User
	 */
	protected function getUser() {
		return $this->context->getUser();
	}

	/**
	 *
	 * @return Title
	 */
	protected function getTitle() {
		return $this->context->getTitle();
	}
}
