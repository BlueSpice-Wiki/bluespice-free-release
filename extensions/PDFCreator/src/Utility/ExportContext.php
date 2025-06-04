<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

use MediaWiki\Page\PageIdentity;
use MediaWiki\User\UserIdentity;

class ExportContext {

	/** @var PageIdentity */
	private $pageIdentity;

	/** @var UserIdentity */
	private $userIdentity;

	/**
	 * @param UserIdentity $userIdentity
	 * @param PageIdentity|null $pageIdentity
	 */
	public function __construct( UserIdentity $userIdentity, ?PageIdentity $pageIdentity = null ) {
		$this->pageIdentity = $pageIdentity;
		$this->userIdentity = $userIdentity;
	}

	/**
	 * @return PageIdentity|null
	 */
	public function getPageIdentity(): ?PageIdentity {
		return $this->pageIdentity;
	}

	/**
	 * @return UserIdentity
	 */
	public function getUserIdentity(): UserIdentity {
		return $this->userIdentity;
	}
}
